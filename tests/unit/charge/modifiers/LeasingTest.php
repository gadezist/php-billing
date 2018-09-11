<?php
/**
 * PHP Billing Library
 *
 * @link      https://github.com/hiqdev/php-billing
 * @package   php-billing
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2017-2018, HiQDev (http://hiqdev.com/)
 */

namespace hiqdev\php\billing\tests\unit\charge\modifiers;

use DateTimeImmutable;
use hiqdev\php\billing\charge\modifiers\addons\MonthPeriod;
use hiqdev\php\billing\charge\modifiers\addons\YearPeriod;
use hiqdev\php\billing\charge\modifiers\Leasing;
use hiqdev\php\billing\tests\unit\action\ActionTest;

/**
 * @author Andrii Vasyliev <sol@hiqdev.com>
 */
class LeasingTest extends ActionTest
{
    protected $reason = 'test reason string';

    protected function buildLeasing($term)
    {
        $month = (new DateTimeImmutable())->modify('first day of this month midnight');

        return (new Leasing())->since($month)->lasts($term);
    }

    public function testCreateMonth()
    {
        $leasing = $this->buildLeasing('12 months');
        $period = $leasing->getTerm();
        $this->assertInstanceOf(MonthPeriod::class, $period);
        $this->assertSame(12, $period->getValue());
    }

    public function testTill()
    {
        $this->expectException(\Exception::class);
        $this->buildLeasing('month')->till('08.2018');
    }

    public function testReason()
    {
        $leasing = $this->buildLeasing('12 months');
        $leasing = $leasing->reason($this->reason);
        $this->assertSame($this->reason, $leasing->getReason()->getValue());
    }

    public function testCreateYear()
    {
        $leasing = $this->buildLeasing('1 year');
        $period = $leasing->getTerm();
        $this->assertInstanceOf(YearPeriod::class, $period);
        $this->assertSame(1, $period->getValue());
    }

    public function testModifyCharge()
    {
        $leasing = $this->buildLeasing('6 months');
        $action = $this->createAction($this->prepaid->multiply(2));
        $charge = $this->calculator->calculateCharge($this->price, $action);
        $charges = $leasing->modifyCharge($charge, $action);
        $this->assertInternalType('array', $charges);
        $this->assertSame(1, count($charges));
        $this->assertEquals($charge, $charges[0]);
    }
}
