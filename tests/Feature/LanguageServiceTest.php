<?php

namespace Tests\Feature;

use App\Services\LanguageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class LanguageServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * LanguageService instance.
     *
     * @var LanguageService
     */
    private $languageService;

    /**
     * Run parent setup function, make sure that database is seeded and load LanguageService.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->seed();

        $this->languageService = App::make(LanguageService::class);
    }

    /**
     * Test LanguageService registered as a singleton.
     *
     * @return void
     */
    public function test_singleton()
    {
        $this->assertSame($this->languageService, App::make(LanguageService::class));
    }

    /**
     * Test LanguageService getLanguageByCode.
     *
     * @return void
     */
    public function test_get_language_by_code()
    {
        $this->assertIsObject($this->languageService->getLanguageByCode('en'));
        $this->assertNull($this->languageService->getLanguageByCode('NONE'));
    }

    /**
     * Test LanguageService getLanguageIdByCode.
     *
     * @return void
     */
    public function test_getLanguage_id_by_code()
    {
        $this->assertIsInt($this->languageService->getLanguageIdByCode('en'));
        $this->assertNull($this->languageService->getLanguageIdByCode('NONE'));
    }
}
