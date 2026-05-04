<?php

use yii\helpers\Url;

class HomeCest
{
    public function ensureThatHomePageWorks(AcceptanceTester $I)
    {
        $I->amOnPage(Url::toRoute('/dashboard/index'));
        $I->see('Dashboard');
        $I->see('Ringkasan Template');
    }
}
