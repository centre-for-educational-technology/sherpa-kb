<?php

namespace Tests\Feature;

use App\Language;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class KnowledgeBaseTestCase extends TestCase
{

    use RefreshDatabase;

    /**
     * Run parent setup function and make sure that database is seeded.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    /**
     * Returns language identifier by code.
     *
     * @param string $code Language code
     * @return int
     * @throws Illuminate\Database\Eloquent\ModelNotFoundException
     */
    protected function getLanguageIdByCode(string $code): int
    {
        return Language::where('code', $code)->firstOrFail()->id;
    }

    /**
     * Creates new user with given attributes and roles.
     *
     * @param array $attributes Attributes to set or override
     * @param array $roles      User roles to assign
     * @return User
     */
    protected function createUser(array $attributes = [], array $roles = []): User
    {
        // Preset language to English unless a value is provided with attributes
        if (!array_key_exists('language_id', $attributes)) {
            $attributes['language_id'] = $this->getLanguageIdByCode('en');
        }

        $user = User::factory()->create($attributes);

        if ($roles) {
            $user->syncRoles($roles);
        }

        return $user;
    }

    /**
     * Creates user with language expert role.
     *
     * @param string $language  Language with default value of en
     * @param array $attributes Attributes to set or override
     * @return User
     */
    protected function createLanguageExpert(string $language = 'en', array $attributes = []): User {
        $attributes['language_id'] = $this->getLanguageIdByCode($language);

        return $this->createUser($attributes, [User::ROLE_LANGUAGE_EXPERT]);
    }

    /**
     * Creates user with master expert role.
     *
     * @param array $attributes Attributes to set or override
     * @return User
     */
    protected function createMasterExpert(array $attributes = []): User
    {
        return $this->createUser($attributes, [User::ROLE_MASTER_EXPERT]);
    }

    /**
     * Creates user with administrator role.
     *
     * @param array $attributes Attributes to set or override
     * @return User
     */
    protected function createAdministrator(array $attributes = []): User
    {
        return $this->createUser($attributes, [User::ROLE_ADMINISTRATOR]);
    }

}
