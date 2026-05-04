<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class FormResult extends ActiveRecord
{
    const STATUS_SUBMITTED = 'submitted';
    const STATUS_LEADER_APPROVED = 'leader_approved';
    const STATUS_SUPERVISOR_APPROVED = 'supervisor_approved';
    const STATUS_CHIEF_APPROVED = 'chief_approved';
    const STATUS_APPROVED = 'approved';

    public static function tableName()
    {
        return 'form_result';
    }

    public function rules()
    {
        return [
            [['no_mesin', 'operator', 'tanggal', 'shift'], 'required'],
            [['template_id', 'leader_id', 'supervisor_id', 'chief_id', 'manager_id', 'created_at', 'updated_at', 'leader_approved_at', 'supervisor_approved_at', 'chief_approved_at', 'manager_approved_at'], 'integer'],
            [['tanggal'], 'safe'],
            [['no_mesin', 'operator', 'shift'], 'string', 'max' => 64],
            [['approval_status'], 'string', 'max' => 64],
            ['approval_status', 'in', 'range' => [
                self::STATUS_SUBMITTED,
                self::STATUS_LEADER_APPROVED,
                self::STATUS_SUPERVISOR_APPROVED,
                self::STATUS_CHIEF_APPROVED,
                self::STATUS_APPROVED,
            ]],
        ];
    }

    public function getDetails()
    {
        return $this->hasMany(FormResultDetail::class, ['form_result_id' => 'id']);
    }

    public function getTemplate()
    {
        return $this->hasOne(FormTemplate::class, ['id' => 'template_id']);
    }

    public function getLeader()
    {
        return $this->hasOne(User::class, ['id' => 'leader_id']);
    }

    public function getSupervisor()
    {
        return $this->hasOne(User::class, ['id' => 'supervisor_id']);
    }

    public function getChief()
    {
        return $this->hasOne(User::class, ['id' => 'chief_id']);
    }

    public function getManager()
    {
        return $this->hasOne(User::class, ['id' => 'manager_id']);
    }

    public function getApprovalStatusLabel()
    {
        $labels = [
            self::STATUS_SUBMITTED => 'Menunggu persetujuan Sub Foreman / Leader',
            self::STATUS_LEADER_APPROVED => 'Menunggu persetujuan Foreman',
            self::STATUS_SUPERVISOR_APPROVED => 'Menunggu persetujuan Chief',
            self::STATUS_CHIEF_APPROVED => 'Menunggu persetujuan Manager',
            self::STATUS_APPROVED => 'Disetujui sepenuhnya',
        ];

        if ($this->approval_status === null) {
            return 'Status tidak diketahui';
        }

        return $labels[$this->approval_status] ?? ucfirst($this->approval_status);
    }

    public function getNextApprovalRole()
    {
        // Approval sequence:
        // 1) submitted -> Sub Foreman / Leader approves and becomes leader_id
        // 2) leader_approved -> Foreman approves and becomes supervisor_id
        // 3) supervisor_approved -> Chief approves and becomes chief_id
        // 4) chief_approved -> Manager approves and becomes manager_id
        switch ($this->approval_status) {
            case self::STATUS_SUBMITTED:
                return User::ROLE_SUBFOREMAN;
            case self::STATUS_LEADER_APPROVED:
                return User::ROLE_FOREMAN;
            case self::STATUS_SUPERVISOR_APPROVED:
                return User::ROLE_CHIEF;
            case self::STATUS_CHIEF_APPROVED:
                return User::ROLE_MANAGER;
            default:
                return null;
        }
    }

    public function getNextApprovalRoleLabel()
    {
        $labels = [
            User::ROLE_FOREMAN => 'Foreman',
            User::ROLE_SUBFOREMAN => 'Sub Foreman / Leader',
            User::ROLE_CHIEF => 'Chief',
            User::ROLE_MANAGER => 'Manager',
        ];

        $role = $this->getNextApprovalRole();
        return $role ? ($labels[$role] ?? ucfirst($role)) : null;
    }

    public function canBeApprovedBy(User $user)
    {
        $role = $this->getNextApprovalRole();
        if ($role === null) {
            return false;
        }

        return Yii::$app->authManager->getAssignment($role, $user->id) !== null;
    }

    public function approveBy(User $user)
    {
        $role = $this->getNextApprovalRole();
        $time = time();

        switch ($role) {
            case User::ROLE_SUBFOREMAN:
                $this->leader_id = $user->id;
                $this->leader_approved_at = $time;
                $this->approval_status = self::STATUS_LEADER_APPROVED;
                break;
            case User::ROLE_FOREMAN:
                $this->supervisor_id = $user->id;
                $this->supervisor_approved_at = $time;
                $this->approval_status = self::STATUS_SUPERVISOR_APPROVED;
                break;
            case User::ROLE_CHIEF:
                $this->chief_id = $user->id;
                $this->chief_approved_at = $time;
                $this->approval_status = self::STATUS_CHIEF_APPROVED;
                break;
            case User::ROLE_MANAGER:
                $this->manager_id = $user->id;
                $this->manager_approved_at = $time;
                $this->approval_status = self::STATUS_APPROVED;
                break;
            default:
                return false;
        }

        return $this->save(false);
    }

    public function getApprovalHistory()
    {
        return [
            [
                'label' => 'Sub Foreman / Leader',
                'user' => $this->leader,
                'approved_at' => $this->leader_approved_at,
            ],
            [
                'label' => 'Foreman',
                'user' => $this->supervisor,
                'approved_at' => $this->supervisor_approved_at,
            ],
            [
                'label' => 'Chief',
                'user' => $this->chief,
                'approved_at' => $this->chief_approved_at,
            ],
            [
                'label' => 'Manager',
                'user' => $this->manager,
                'approved_at' => $this->manager_approved_at,
            ],
        ];
    }

    public static function getPendingApprovalNotifications(User $user, $limit = 5)
    {
        $roles = array_keys(Yii::$app->authManager->getRolesByUser($user->id));
        $statusRoleMap = [
            self::STATUS_SUBMITTED => User::ROLE_SUBFOREMAN,
            self::STATUS_LEADER_APPROVED => User::ROLE_FOREMAN,
            self::STATUS_SUPERVISOR_APPROVED => User::ROLE_CHIEF,
            self::STATUS_CHIEF_APPROVED => User::ROLE_MANAGER,
        ];

        $notifications = [];
        foreach ($statusRoleMap as $status => $roleName) {
            if (!in_array($roleName, $roles, true)) {
                continue;
            }

            $results = self::find()
                ->select(['id', 'no_mesin', 'operator', 'tanggal', 'shift'])
                ->where(['approval_status' => $status])
                ->orderBy(['created_at' => SORT_DESC])
                ->limit($limit)
                ->all();

            foreach ($results as $result) {
                $notifications[] = [
                    'id' => $result->id,
                    'title' => 'Form #' . $result->id,
                    'message' => sprintf('%s / %s / %s', $result->no_mesin, $result->operator, $result->tanggal),
                    'status' => $result->getApprovalStatusLabel(),
                    'url' => ['/form-result/view', 'id' => $result->id],
                    'created_at' => $result->created_at,
                ];
            }
        }

        usort($notifications, function ($a, $b) {
            return $b['created_at'] <=> $a['created_at'];
        });

        return array_slice($notifications, 0, $limit);
    }
}
