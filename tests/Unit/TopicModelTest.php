<?php

namespace Tests\Unit;

use App\Topic;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use Spatie\Activitylog\Traits\LogsActivity;

class TopicModelTest extends TestCase
{
    /**
     * Test used traits and extended class.
     *
     * @return void
     */
    public function test_uses_traits_and_extends()
    {
        $uses = class_uses_recursive(Topic::class);

        $this->assertContains(HasFactory::class, $uses);
        $this->assertContains(LogsActivity::class, $uses);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, new Topic);
    }

    /**
     * Test Topic LogsActivity trait constants.
     *
     * @return void
     */
    public function test_logs_activity_configuration()
    {
        $logAttributes = new ReflectionProperty(Topic::class, 'logAttributes');
        $this->assertTrue($logAttributes->isProtected());
        $this->assertTrue($logAttributes->isStatic());
        $logAttributes->setAccessible(true);
        $this->assertEquals(['*'], $logAttributes->getValue());

        $submitEmptyLogs = new ReflectionProperty(Topic::class, 'submitEmptyLogs');
        $this->assertTrue($submitEmptyLogs->isProtected());
        $this->assertTrue($submitEmptyLogs->isStatic());
        $submitEmptyLogs->setAccessible(true);
        $this->assertFalse($submitEmptyLogs->getValue());
    }
}
