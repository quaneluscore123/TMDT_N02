<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApiDocsTest extends TestCase
{
    public function test_docs_page_renders(): void
    {
        $this->get('/docs')->assertOk();
    }

    public function test_postman_and_openapi_return_404_instead_of_500_when_not_generated(): void
    {
        Storage::fake('local');

        $this->get('/docs.postman')->assertNotFound();
        $this->get('/docs.openapi')->assertNotFound();
    }

    public function test_postman_collection_is_served_when_generated(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('scribe/collection.json', '{"info":{"name":"x"}}');

        $this->get('/docs.postman')->assertOk()->assertJsonPath('info.name', 'x');
    }
}
