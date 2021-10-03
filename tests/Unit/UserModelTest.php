<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;
use PHPUnit\Framework\TestCase;
use App\User;
use ReflectionMethod;
use ReflectionProperty;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

class UserModelTest extends TestCase
{
    /**
     * Test User role constants.
     *
     * @return void
     */
    public function test_role_constants()
    {
        $this->assertEquals('expert', User::ROLE_LANGUAGE_EXPERT);
        $this->assertEquals('master', User::ROLE_MASTER_EXPERT);
        $this->assertEquals('administrator', User::ROLE_ADMINISTRATOR);
    }

    /**
     * Test used traits and extended class.
     *
     * @return void
     */
    public function test_uses_traits_and_extends()
    {
        $uses = class_uses_recursive(User::class);

        $this->assertContains(HasFactory::class, $uses);
        $this->assertcontains(Notifiable::class, $uses);
        $this->assertContains(HasRoles::class, $uses);
        $this->assertContains(LogsActivity::class, $uses);
        $this->assertInstanceOf(\Illuminate\Foundation\Auth\User::class, new User);
    }

    /**
     * Test that roles() method is overridden correctly.
     *
     * @return void
     */
    public function test_roles_override()
    {
        $this->assertTrue(method_exists(User::class, 'traitRoles'));

        $traitRoles = new ReflectionMethod(User::class, 'traitRoles');
        $this->assertTrue($traitRoles->isPrivate());

        $this->assertTrue(method_exists(User::class, 'roles'));

        $roles = new ReflectionMethod(User::class, 'roles');
        $this->assertTrue($roles->isPublic());
        $this->assertEquals(BelongsToMany::class, $roles->getReturnType()->getName());
    }

    /**
     * Test that languages() method exists and returns correct type.
     *
     * @return void
     */
    public function test_language()
    {
        $this->assertTrue(method_exists(User::class, 'language'));

        $languageReflection = new ReflectionMethod(User::class, 'language');
        $this->assertTrue($languageReflection->isPublic());
        $this->assertEquals(BelongsTo::class, $languageReflection->getReturnType()->getName());
    }

    /**
     * Test User LogsActivity trait constants.
     *
     * @return void
     */
    public function test_logs_activity_configuration()
    {
        $logAttributes = new ReflectionProperty(User::class, 'logAttributes');
        $this->assertTrue($logAttributes->isProtected());
        $this->assertTrue($logAttributes->isStatic());
        $logAttributes->setAccessible(true);
        $this->assertEquals(['*'], $logAttributes->getValue());

        $submitEmptyLogs = new ReflectionProperty(User::class, 'submitEmptyLogs');
        $this->assertTrue($submitEmptyLogs->isProtected());
        $this->assertTrue($submitEmptyLogs->isStatic());
        $submitEmptyLogs->setAccessible(true);
        $this->assertFalse($submitEmptyLogs->getValue());

        $logAttributesToIgnore = new ReflectionProperty(User::class, 'logAttributesToIgnore');
        $this->assertTrue($logAttributesToIgnore->isProtected());
        $this->assertTrue($logAttributesToIgnore->isStatic());
        $logAttributesToIgnore->setAccessible(true);
        $this->assertEquals(['password', 'remember_token'], $logAttributesToIgnore->getValue());

        $ignoreChangedAttributes = new ReflectionProperty(User::class, 'ignoreChangedAttributes');
        $this->assertTrue($ignoreChangedAttributes->isProtected());
        $this->assertTrue($ignoreChangedAttributes->isStatic());
        $ignoreChangedAttributes->setAccessible(true);
        $this->assertEquals(['remember_token'], $ignoreChangedAttributes->getValue());
    }

    /**
     *
     * Test User attributes.
     *
     * @returns void
     * @throws \ReflectionException
     */
    public function test_attributes()
    {
        $user = new User;

        $fillable = new ReflectionProperty($user, 'fillable');
        $this->assertTrue($fillable->isProtected());
        $fillable->setAccessible(true);
        $this->assertEquals(['name', 'email', 'password'], $fillable->getValue($user));

        $hidden = new ReflectionProperty($user, 'hidden');
        $this->assertTrue($hidden->isProtected());
        $hidden->setAccessible(true);
        $this->assertEquals(['password', 'remember_token'], $hidden->getValue($user));

        $casts = new ReflectionProperty($user, 'casts');
        $this->assertTrue($casts->isProtected());
        $casts->setAccessible(true);
        $this->assertEquals(['email_verified_at' => 'datetime'], $casts->getValue($user));
    }

    /**
     * Tests role specific check methods.
     *
     * @return void
     */
    public function test_role_check_methods()
    {
        $this->assertTrue(method_exists(User::class, 'isLanguageExpert'));
        $isLanguageExpert = new ReflectionMethod(User::class, 'isLanguageExpert');
        $this->assertTrue($isLanguageExpert->isPublic());
        $this->assertEquals('bool', $isLanguageExpert->getReturnType()->getName());

        $this->assertTrue(method_exists(User::class, 'isMasterExpert'));
        $isMasterExpert = new ReflectionMethod(User::class, 'isMasterExpert');
        $this->assertTrue($isMasterExpert->isPublic());
        $this->assertEquals('bool', $isMasterExpert->getReturnType()->getName());

        $this->assertTrue(method_exists(User::class, 'isAdministrator'));
        $isAdministrator = new ReflectionMethod(User::class, 'isAdministrator');
        $this->assertTrue($isAdministrator->isPublic());
        $this->assertEquals('bool', $isAdministrator->getReturnType()->getName());
    }

}
