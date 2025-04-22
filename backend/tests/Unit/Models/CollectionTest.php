<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Collection;
use App\Models\Adhkar;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_has_required_fields()
    {
        $collection = Collection::create([
            'name' => 'Morning Adhkar',
            'type' => 'daily',
            'description' => 'Morning remembrance collection',
            'icon' => 'sun',
        ]);

        $this->assertEquals('Morning Adhkar', $collection->name);
        $this->assertEquals('daily', $collection->type);
        $this->assertEquals('Morning remembrance collection', $collection->description);
        $this->assertEquals('sun', $collection->icon);
        $this->assertEquals('morning-adhkar', $collection->slug);
    }

    public function test_collection_can_have_adhkar()
    {
        $collection = Collection::factory()->create();
        $adhkar = Adhkar::factory()->create([
            'title' => 'First Dhikr',
            'arabic_text' => 'بِسْمِ اللَّهِ الرَّحْمَنِ الرَّحِيمِ',
            'translation' => 'In the name of Allah, the Most Gracious, the Most Merciful',
            'count' => 33,
            'prefix' => 'سبحان الله',
            'collection_id' => $collection->id
        ]);

        $this->assertInstanceOf(Collection::class, $adhkar->collection);
        $this->assertEquals($collection->id, $adhkar->collection->id);
    }

    public function test_adhkar_relationship()
    {
        $collection = Collection::factory()->create();
        
        // Test that the adhkar method returns a HasMany relationship
        $this->assertInstanceOf(HasMany::class, $collection->adhkar());
        
        // Test that the relationship is properly set up
        $this->assertEquals(Adhkar::class, get_class($collection->adhkar()->getRelated()));
        $this->assertEquals('collection_id', $collection->adhkar()->getForeignKeyName());
    }

    public function test_slug_is_generated_automatically()
    {
        $collection = Collection::create([
            'name' => 'Test Collection Name',
            'type' => 'daily',
        ]);

        $this->assertEquals('test-collection-name', $collection->slug);
    }

    public function test_slug_is_unique()
    {
        // Create first collection
        $collection1 = Collection::create([
            'name' => 'Test Collection',
            'type' => 'daily',
        ]);

        // Create second collection with same name
        $collection2 = Collection::create([
            'name' => 'Test Collection',
            'type' => 'daily',
        ]);

        $this->assertEquals('test-collection', $collection1->slug);
        $this->assertEquals('test-collection-1', $collection2->slug);
    }

    public function test_slug_is_updated_when_name_changes()
    {
        $collection = Collection::create([
            'name' => 'Original Name',
            'type' => 'daily',
        ]);

        $this->assertEquals('original-name', $collection->slug);

        $collection->name = 'Updated Name';
        $collection->save();

        $this->assertEquals('updated-name', $collection->slug);
    }

    public function test_slug_is_not_updated_when_other_fields_change()
    {
        $collection = Collection::create([
            'name' => 'Test Collection',
            'type' => 'daily',
            'description' => 'Original description',
        ]);

        $originalSlug = $collection->slug;

        $collection->description = 'Updated description';
        $collection->save();

        $this->assertEquals($originalSlug, $collection->slug);
    }

    public function test_fillable_attributes()
    {
        $collection = new Collection();
        
        $this->assertTrue(in_array('name', $collection->getFillable()));
        $this->assertTrue(in_array('type', $collection->getFillable()));
        $this->assertTrue(in_array('description', $collection->getFillable()));
        $this->assertTrue(in_array('icon', $collection->getFillable()));
        $this->assertTrue(in_array('slug', $collection->getFillable()));
    }

    public function test_slug_generation_with_special_characters()
    {
        // Test with special characters
        $collection1 = Collection::create([
            'name' => 'Test Collection with Special Chars!',
            'type' => 'daily',
        ]);
        $this->assertEquals('test-collection-with-special-chars', $collection1->slug);
        
        // Test with multiple spaces
        $collection2 = Collection::create([
            'name' => 'Test  Collection  with  Multiple  Spaces',
            'type' => 'daily',
        ]);
        $this->assertEquals('test-collection-with-multiple-spaces', $collection2->slug);
        
        // Test with non-ASCII characters (Arabic text)
        $collection3 = Collection::create([
            'name' => 'Test Collection with Arabic بسم الله',
            'type' => 'daily',
        ]);
        $this->assertEquals('test-collection-with-arabic-bsm-allh', $collection3->slug);
    }

    public function test_boot_method_creating_event()
    {
        // Test that slug is generated on creation
        $collection = Collection::create([
            'name' => 'Test Collection',
            'type' => 'daily',
        ]);
        $this->assertEquals('test-collection', $collection->slug);

        // Test that provided slug is not overwritten
        $collection2 = Collection::create([
            'name' => 'Test Collection',
            'type' => 'daily',
            'slug' => 'custom-slug',
        ]);
        $this->assertEquals('custom-slug', $collection2->slug);
    }

    public function test_boot_method_updating_event()
    {
        $collection = Collection::create([
            'name' => 'Original Name',
            'type' => 'daily',
        ]);

        // Test that slug is updated when name changes
        $collection->name = 'Updated Name';
        $collection->save();
        $this->assertEquals('updated-name', $collection->slug);

        // Test that slug is not updated when name is not changed
        $collection->description = 'New description';
        $collection->save();
        $this->assertEquals('updated-name', $collection->slug);

        // Test that provided slug is not overwritten
        $collection->name = 'Another Name';
        $collection->slug = 'custom-slug';
        $collection->save();
        $this->assertEquals('custom-slug', $collection->slug);
    }

    public function test_generate_unique_slug_with_multiple_duplicates()
    {
        // Create multiple collections with the same name
        $collections = [];
        for ($i = 0; $i < 3; $i++) {
            $collections[] = Collection::create([
                'name' => 'Test Collection',
                'type' => 'daily',
            ]);
        }

        // Verify that each collection has a unique slug
        $this->assertEquals('test-collection', $collections[0]->slug);
        $this->assertEquals('test-collection-1', $collections[1]->slug);
        $this->assertEquals('test-collection-2', $collections[2]->slug);
    }
} 