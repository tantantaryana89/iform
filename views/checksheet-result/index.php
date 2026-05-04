<?php
use yii\helpers\Html;

$this->title = 'Daftar Checksheet Results';

$totalResults = count($results ?? []);
$uniqueMesin = count(array_unique(array_filter(array_map(static fn($r) => (string)($r->mesin ?? ''), $results ?? []))));
$latestSubmitted = null;
if (!empty($results)) {
    $latestSubmitted = $results[0]->submitted_at ?? null;
}

if (!function_exists('formatSubmitDateTime')) {
    function formatSubmitDateTime($value): string
    {
        $text = trim((string)$value);
        if ($text === '') {
            return '-';
        }

        $timestamp = strtotime($text);
        if ($timestamp === false) {
            return Html::encode($text);
        }

        return date('d M Y H:i', $timestamp);
    }
}

if (!function_exists('normalizeShiftLabel')) {
    function normalizeShiftLabel($value): string
    {
        $raw = trim((string)$value);
        if ($raw === '') {
            return '-';
        }

        if (preg_match('/(\d+)/', $raw, $m)) {
            return 'Shift ' . (int)$m[1];
        }

        return strtoupper($raw);
    }
}
?>

<style>
    .checks-result-page .summary-card {
        border: 1px solid #e8edf3;
        border-radius: 12px;
        background: #fff;
        padding: 14px 16px;
        height: 100%;
    }
    .checks-result-page .summary-label {
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }
    .checks-result-page .summary-value {
        color: #1f2937;
        font-size: 24px;
        font-weight: 700;
        line-height: 1.2;
        margin-top: 4px;
    }
    .checks-result-page .index-card {
        border: 1px solid #e8edf3;
        border-radius: 12px;
        background: #fff;
    }
    .checks-result-page .toolbar {
        border-bottom: 1px solid #eef2f7;
        padding: 14px 16px;
    }
    .checks-result-page .table thead th {
        font-size: 12px;
        text-transform: uppercase;
        color: #6b7280;
        border-bottom: 1px solid #e9eef5;
        white-space: nowrap;
        position: sticky;
        top: 0;
        background: #f8fafc;
        z-index: 2;
    }
    .checks-result-page .table tbody td {
        vertical-align: middle;
        border-color: #f0f3f8;
    }
    .checks-result-page .result-id {
        font-weight: 700;
        color: #1f2937;
    }
    .checks-result-page .mesin-name {
        font-weight: 600;
        color: #111827;
    }
    .checks-result-page .submitted-muted {
        color: #6b7280;
        font-size: 12px;
    }
    .checks-result-page .action-group {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }
    .checks-result-page .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: #6b7280;
    }
    .checks-result-page .results-scroll {
        max-height: 62vh;
        overflow-y: auto;
        overflow-x: auto;
        border-radius: 0 0 12px 12px;
    }
</style>

<div class="checks-result-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h3 class="mb-1"><?= Html::encode($this->title) ?></h3>
            <div class="text-muted">Monitoring hasil submit checksheet dari aplikasi.</div>
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="summary-card">
                <div class="summary-label">Total Result</div>
                <div class="summary-value"><?= (int)$totalResults ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card">
                <div class="summary-label">Mesin Aktif</div>
                <div class="summary-value"><?= (int)$uniqueMesin ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="summary-card">
                <div class="summary-label">Submit Terbaru</div>
                <div class="summary-value" style="font-size:18px;">
                    <?= $latestSubmitted ? Html::encode(formatSubmitDateTime($latestSubmitted)) : '-' ?>
                </div>
            </div>
        </div>
    </div>

    <div class="index-card">
        <div class="toolbar d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div class="fw-semibold">Daftar Transaksi</div>
            <div class="d-flex gap-2">
                <input
                    id="result-search"
                    type="text"
                    class="form-control form-control-sm"
                    placeholder="Cari mesin / shift / tanggal..."
                    style="min-width: 280px;"
                >
            </div>
        </div>

        <div class="table-responsive results-scroll">
            <table class="table table-hover mb-0" id="result-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th>Mesin</th>
                        <th style="width: 130px;">Shift</th>
                        <th style="width: 210px;">Submitted At</th>
                        <th style="width: 330px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($results)): ?>
                    <tr>
                        <td colspan="5" class="empty-state">
                            Belum ada data checksheet.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($results as $r): ?>
                        <tr data-search="<?= Html::encode(strtolower(trim(($r->mesin ?? '') . ' ' . ($r->shift ?? '') . ' ' . ($r->submitted_at ?? '')))) ?>">
                            <td>
                                <span class="result-id">#<?= (int)$r->id ?></span>
                            </td>
                            <td>
                                <div class="mesin-name"><?= Html::encode((string)$r->mesin) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= Html::encode(normalizeShiftLabel($r->shift)) ?></span>
                            </td>
                            <td>
                                <div><?= Html::encode(formatSubmitDateTime($r->submitted_at)) ?></div>
                                <div class="submitted-muted"><?= Html::encode((string)$r->submitted_at) ?></div>
                            </td>
                            <td>
                                <div class="action-group">
                                    <?= Html::a('Detail', ['view', 'id' => $r->id], ['class' => 'btn btn-primary btn-sm']) ?>
                                    <?= Html::a('Export Transaksi', ['export-original', 'id' => $r->id], ['class' => 'btn btn-outline-success btn-sm']) ?>
                                    <?= Html::a('Export Bulanan', ['export-excel', 'id' => $r->id], ['class' => 'btn btn-outline-secondary btn-sm']) ?>

                                    <?= Html::a(
                                        '📄 PDF',
                                        ['export-pdf', 'id' => $r->id],
                                        [
                                            'class' => 'btn btn-outline-danger btn-sm',
                                            'target' => '_blank'
                                        ]
                                    ) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    (function () {
        const searchInput = document.getElementById('result-search');
        const table = document.getElementById('result-table');
        if (!searchInput || !table) {
            return;
        }

        const rows = Array.from(table.querySelectorAll('tbody tr'));
        searchInput.addEventListener('input', function () {
            const keyword = this.value.trim().toLowerCase();
            rows.forEach(function (row) {
                const haystack = (row.getAttribute('data-search') || '').toLowerCase();
                if (!keyword || haystack.indexOf(keyword) !== -1) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    })();
</script>
