# Recipe: File Upload System

## 🎯 Mikor Használd?

- **Képfeltöltés** és -kezelés funkcióhoz
- **Dokumentum management** rendszerhez
- **Avatar/profil kép** feltöltéshez
- **Media library** fejlesztéséhez
- **Fájl optimalizáció** és -kompresszió szükséges

## ⏱️ Implementációs Idő: 3-4 óra

## 📋 Előfeltételek

- ✅ Core boilerplate telepítve
- ✅ Alapvető Laravel tudás
- ✅ File system és storage ismerete
- ⚠️ Image manipulation alapok ajánlottak

## 🚀 Quick Start

```bash
# 1. Spatie Media Library telepítése
composer require spatie/laravel-medialibrary

# 2. Image intervention telepítése (képfeldolgozáshoz)
composer require intervention/image

# 3. Migráció publikálás és futtatás
php artisan vendor:publish --provider="Spatie\MediaLibrary\MediaLibraryServiceProvider" --tag="medialibrary-migrations"
php artisan migrate

# 4. Storage link létrehozása
php artisan storage:link

# 5. Filament file upload plugin telepítése
composer require filament/spatie-laravel-media-library-plugin
```

## 📖 Részletes Implementáció

### 1. Storage Konfiguráció

#### Storage Beállítások

```php
// config/filesystems.php
'disks' => [
    'local' => [
        'driver' => 'local',
        'root' => storage_path('app'),
        'throw' => false,
    ],

    'public' => [
        'driver' => 'local',
        'root' => storage_path('app/public'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'public',
        'throw' => false,
    ],

    'uploads' => [
        'driver' => 'local',
        'root' => storage_path('app/public/uploads'),
        'url' => env('APP_URL').'/storage/uploads',
        'visibility' => 'public',
    ],

    's3' => [
        'driver' => 's3',
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
        'throw' => false,
    ],
],

'default' => env('FILESYSTEM_DISK', 'local'),
```

#### Environment Változók

```env
# Storage beállítások
FILESYSTEM_DISK=public
MEDIA_DISK=public

# S3 konfiguráció (opcionális)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_URL=

# Upload limitek
UPLOAD_MAX_SIZE=10240  # 10MB in KB
UPLOAD_ALLOWED_TYPES=jpg,jpeg,png,gif,pdf,doc,docx
```

### 2. Media Library Integráció

#### Base Model Media Trait

```php
// app/Traits/HasMediaTrait.php
<?php

namespace App\Traits;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasMediaTrait
{
    use InteractsWithMedia;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif']);

        $this->addMediaCollection('avatars')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png']);

        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'application/msword']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif']);
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->quality(80)
            ->performOnCollections('default', 'gallery', 'avatars');

        $this->addMediaConversion('preview')
            ->width(800)
            ->height(600)
            ->quality(90)
            ->performOnCollections('default', 'gallery');

        $this->addMediaConversion('avatar')
            ->width(200)
            ->height(200)
            ->quality(90)
            ->performOnCollections('avatars');
    }

    // Helper methods
    public function getAvatarUrl(?string $conversion = 'avatar'): ?string
    {
        $media = $this->getFirstMedia('avatars');
        return $media ? $media->getUrl($conversion) : null;
    }

    public function getGalleryImages(string $conversion = 'preview'): array
    {
        return $this->getMedia('gallery')
            ->map(fn($media) => [
                'id' => $media->id,
                'url' => $media->getUrl($conversion),
                'thumb_url' => $media->getUrl('thumb'),
                'name' => $media->name,
                'size' => $media->size,
            ])
            ->toArray();
    }
}
```

#### User Model Media Integration

```php
// app/Models/User.php
<?php

namespace App\Models;

use App\Traits\HasMediaTrait;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\MediaLibrary\HasMedia;

class User extends Authenticatable implements HasMedia
{
    use HasMediaTrait;
    
    // ... existing user code ...

    protected $appends = ['avatar_url'];

    public function getAvatarUrlAttribute(): ?string
    {
        return $this->getAvatarUrl();
    }
}
```

### 3. File Upload Service

#### Upload Service

```php
// app/Services/FileUploadService.php
<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;
use Spatie\MediaLibrary\HasMedia;

class FileUploadService
{
    protected array $allowedTypes;
    protected int $maxSize;

    public function __construct()
    {
        $this->allowedTypes = explode(',', config('app.upload_allowed_types', 'jpg,jpeg,png,gif,pdf'));
        $this->maxSize = config('app.upload_max_size', 10240) * 1024; // Convert KB to bytes
    }

    public function uploadToModel(HasMedia $model, UploadedFile $file, string $collection = 'default'): \Spatie\MediaLibrary\MediaCollections\Models\Media
    {
        $this->validateFile($file);

        return $model->addMediaFromRequest('file')
            ->toMediaCollection($collection);
    }

    public function uploadMultipleToModel(HasMedia $model, array $files, string $collection = 'default'): array
    {
        $uploaded = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $uploaded[] = $this->uploadToModel($model, $file, $collection);
            }
        }

        return $uploaded;
    }

    public function uploadToStorage(UploadedFile $file, string $disk = 'public', ?string $path = null): array
    {
        $this->validateFile($file);

        $path = $path ?? 'uploads/' . date('Y/m');
        $filename = $this->generateUniqueFilename($file);
        $fullPath = $path . '/' . $filename;

        $stored = Storage::disk($disk)->putFileAs($path, $file, $filename);

        return [
            'path' => $stored,
            'url' => Storage::disk($disk)->url($stored),
            'filename' => $filename,
            'original_name' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    public function optimizeImage(UploadedFile $file, array $options = []): UploadedFile
    {
        if (!$this->isImage($file)) {
            return $file;
        }

        $options = array_merge([
            'quality' => 80,
            'max_width' => 1920,
            'max_height' => 1080,
        ], $options);

        $image = Image::make($file->path());

        // Resize if needed
        if ($image->width() > $options['max_width'] || $image->height() > $options['max_height']) {
            $image->resize($options['max_width'], $options['max_height'], function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Save with quality compression
        $tempPath = tempnam(sys_get_temp_dir(), 'optimized_');
        $image->save($tempPath, $options['quality']);

        // Create new UploadedFile instance
        return new UploadedFile(
            $tempPath,
            $file->getClientOriginalName(),
            $file->getMimeType(),
            null,
            true
        );
    }

    protected function validateFile(UploadedFile $file): void
    {
        // Size validation
        if ($file->getSize() > $this->maxSize) {
            throw new \InvalidArgumentException(
                "File size exceeds maximum allowed size of " . ($this->maxSize / 1024 / 1024) . "MB"
            );
        }

        // Type validation
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->allowedTypes)) {
            throw new \InvalidArgumentException(
                "File type '{$extension}' is not allowed. Allowed types: " . implode(', ', $this->allowedTypes)
            );
        }

        // Additional security checks
        if (!$file->isValid()) {
            throw new \InvalidArgumentException("Invalid file upload");
        }
    }

    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $basename = Str::slug($basename);
        
        return $basename . '_' . time() . '_' . Str::random(8) . '.' . $extension;
    }

    protected function isImage(UploadedFile $file): bool
    {
        return str_starts_with($file->getMimeType(), 'image/');
    }

    public function deleteFromModel(HasMedia $model, int $mediaId): bool
    {
        $media = $model->getMedia()->find($mediaId);
        
        if ($media) {
            $media->delete();
            return true;
        }
        
        return false;
    }

    public function deleteFromStorage(string $path, string $disk = 'public'): bool
    {
        return Storage::disk($disk)->delete($path);
    }
}
```

### 4. Filament Integration

#### User Resource File Upload

```php
// app/Filament/Resources/UserResource.php - form method kiegészítése
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

public static function form(Form $form): Form
{
    return $form->schema([
        // ... existing fields ...

        SpatieMediaLibraryFileUpload::make('avatar')
            ->collection('avatars')
            ->image()
            ->imageEditor()
            ->imageEditorAspectRatios([
                '1:1',
            ])
            ->maxSize(2048)
            ->columnSpanFull(),

        SpatieMediaLibraryFileUpload::make('documents')
            ->collection('documents')
            ->multiple()
            ->reorderable()
            ->acceptedFileTypes(['application/pdf', 'application/msword'])
            ->maxSize(10240)
            ->columnSpanFull(),
    ]);
}

// Table column kiegészítése
public static function table(Table $table): Table
{
    return $table->columns([
        // ... existing columns ...

        SpatieMediaLibraryImageColumn::make('avatar')
            ->collection('avatars')
            ->conversion('thumb')
            ->circular(),
    ]);
}
```

#### Standalone Media Resource

```php
// app/Filament/Resources/MediaResource.php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MediaResource\Pages;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaResource extends Resource
{
    protected static ?string $model = Media::class;
    protected static ?string $navigationIcon = 'heroicon-o-photo';
    protected static ?string $navigationGroup = 'Media';
    protected static ?string $navigationLabel = 'Médiafájlok';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(255),

            TextInput::make('file_name')
                ->disabled(),

            TextInput::make('collection_name')
                ->disabled(),

            FileUpload::make('file')
                ->disk('public')
                ->directory('uploads/media')
                ->visibility('public'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('preview')
                    ->getStateUsing(function (Media $record) {
                        return $record->hasGeneratedConversion('thumb') 
                            ? $record->getUrl('thumb') 
                            : null;
                    })
                    ->size(60),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('collection_name')
                    ->sortable(),

                TextColumn::make('mime_type')
                    ->badge(),

                TextColumn::make('human_readable_size')
                    ->label('Size'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMedia::route('/'),
            'create' => Pages\CreateMedia::route('/create'),
            'edit' => Pages\EditMedia::route('/{record}/edit'),
        ];
    }
}
```

### 5. Frontend Components

#### File Upload Vue Component

```vue
<!-- resources/js/Components/FileUpload.vue -->
<template>
    <div class="file-upload-container">
        <div 
            class="dropzone"
            :class="{ 'dragover': isDragOver }"
            @dragover.prevent="handleDragOver"
            @dragleave.prevent="handleDragLeave"
            @drop.prevent="handleDrop"
        >
            <input
                ref="fileInput"
                type="file"
                :multiple="multiple"
                :accept="accept"
                @change="handleFileSelect"
                class="hidden"
            />
            
            <div class="upload-content" @click="$refs.fileInput.click()">
                <svg class="upload-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                </svg>
                <p class="upload-text">
                    Kattints a fájl kiválasztásához vagy húzd ide
                </p>
                <p class="upload-subtext">
                    Támogatott formátumok: {{ accept }}
                </p>
            </div>
        </div>

        <div v-if="files.length > 0" class="file-list">
            <div v-for="(file, index) in files" :key="index" class="file-item">
                <img v-if="isImage(file)" :src="getPreviewUrl(file)" class="file-preview" />
                <div class="file-info">
                    <p class="file-name">{{ file.name }}</p>
                    <p class="file-size">{{ formatFileSize(file.size) }}</p>
                </div>
                <button @click="removeFile(index)" class="remove-button">
                    ✕
                </button>
            </div>
        </div>

        <div v-if="uploadProgress > 0 && uploadProgress < 100" class="progress-bar">
            <div class="progress-fill" :style="{ width: uploadProgress + '%' }"></div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'FileUpload',
    props: {
        multiple: { type: Boolean, default: false },
        accept: { type: String, default: 'image/*' },
        maxSize: { type: Number, default: 10 * 1024 * 1024 }, // 10MB
        modelValue: { type: Array, default: () => [] }
    },
    emits: ['update:modelValue', 'upload-complete', 'upload-error'],
    data() {
        return {
            files: [],
            isDragOver: false,
            uploadProgress: 0
        }
    },
    methods: {
        handleDragOver() {
            this.isDragOver = true;
        },
        handleDragLeave() {
            this.isDragOver = false;
        },
        handleDrop(e) {
            this.isDragOver = false;
            this.processFiles(Array.from(e.dataTransfer.files));
        },
        handleFileSelect(e) {
            this.processFiles(Array.from(e.target.files));
        },
        processFiles(newFiles) {
            const validFiles = newFiles.filter(file => {
                if (file.size > this.maxSize) {
                    alert(`${file.name} túl nagy. Maximum méret: ${this.formatFileSize(this.maxSize)}`);
                    return false;
                }
                return true;
            });

            if (this.multiple) {
                this.files = [...this.files, ...validFiles];
            } else {
                this.files = validFiles.slice(0, 1);
            }

            this.$emit('update:modelValue', this.files);
            this.uploadFiles();
        },
        removeFile(index) {
            this.files.splice(index, 1);
            this.$emit('update:modelValue', this.files);
        },
        async uploadFiles() {
            if (this.files.length === 0) return;

            const formData = new FormData();
            this.files.forEach(file => {
                formData.append('files[]', file);
            });

            try {
                const response = await fetch('/api/upload', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                if (response.ok) {
                    const result = await response.json();
                    this.$emit('upload-complete', result);
                } else {
                    throw new Error('Upload failed');
                }
            } catch (error) {
                this.$emit('upload-error', error);
            }
        },
        isImage(file) {
            return file.type.startsWith('image/');
        },
        getPreviewUrl(file) {
            return URL.createObjectURL(file);
        },
        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
    }
}
</script>

<style scoped>
.dropzone {
    border: 2px dashed #d1d5db;
    border-radius: 8px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.dropzone.dragover {
    border-color: #3b82f6;
    background-color: #eff6ff;
}

.upload-icon {
    width: 48px;
    height: 48px;
    margin: 0 auto 1rem;
    color: #6b7280;
}

.file-list {
    margin-top: 1rem;
}

.file-item {
    display: flex;
    align-items: center;
    padding: 0.5rem;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    margin-bottom: 0.5rem;
}

.file-preview {
    width: 40px;
    height: 40px;
    object-fit: cover;
    border-radius: 4px;
    margin-right: 0.5rem;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background-color: #e5e7eb;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 1rem;
}

.progress-fill {
    height: 100%;
    background-color: #3b82f6;
    transition: width 0.3s ease;
}
</style>
```

### 6. API Endpoints

#### Upload Controller

```php
// app/Http/Controllers/Api/UploadController.php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UploadController extends Controller
{
    protected FileUploadService $uploadService;

    public function __construct(FileUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'files' => 'required|array',
            'files.*' => 'file|max:10240', // 10MB
            'collection' => 'string|nullable',
            'model_type' => 'string|nullable',
            'model_id' => 'integer|nullable',
        ]);

        try {
            $uploads = [];
            $collection = $request->input('collection', 'default');

            foreach ($request->file('files') as $file) {
                if ($request->input('model_type') && $request->input('model_id')) {
                    // Upload to model
                    $modelClass = $request->input('model_type');
                    $model = $modelClass::findOrFail($request->input('model_id'));
                    
                    $media = $this->uploadService->uploadToModel($model, $file, $collection);
                    $uploads[] = [
                        'id' => $media->id,
                        'url' => $media->getUrl(),
                        'thumb_url' => $media->getUrl('thumb'),
                        'name' => $media->name,
                    ];
                } else {
                    // Upload to storage
                    $result = $this->uploadService->uploadToStorage($file);
                    $uploads[] = $result;
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Files uploaded successfully',
                'uploads' => $uploads,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'media_id' => 'required|integer|exists:media,id',
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
        ]);

        try {
            $modelClass = $request->input('model_type');
            $model = $modelClass::findOrFail($request->input('model_id'));
            
            $deleted = $this->uploadService->deleteFromModel($model, $request->input('media_id'));

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully',
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found',
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Delete failed: ' . $e->getMessage(),
            ], 400);
        }
    }
}
```

## 🧪 Testing

### Upload Tests

```php
// tests/Feature/FileUploadTest.php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FileUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_user_can_upload_avatar()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->image('avatar.jpg', 300, 300);

        $uploadService = app(FileUploadService::class);
        $media = $uploadService->uploadToModel($user, $file, 'avatars');

        $this->assertNotNull($media);
        $this->assertEquals('avatars', $media->collection_name);
        $this->assertNotNull($user->getAvatarUrl());
    }

    public function test_upload_validates_file_size()
    {
        $user = User::factory()->create();
        // Create a fake file larger than allowed
        $file = UploadedFile::fake()->create('large_file.pdf', 20000); // 20MB

        $uploadService = app(FileUploadService::class);

        $this->expectException(\InvalidArgumentException::class);
        $uploadService->uploadToModel($user, $file);
    }

    public function test_upload_validates_file_type()
    {
        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('malicious.exe', 100);

        $uploadService = app(FileUploadService::class);

        $this->expectException(\InvalidArgumentException::class);
        $uploadService->uploadToModel($user, $file);
    }

    public function test_api_upload_endpoint()
    {
        $user = $this->createUserWithPermissions(['media.upload']);
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/upload', [
                'files' => [$file],
                'model_type' => User::class,
                'model_id' => $user->id,
                'collection' => 'avatars',
            ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'uploads' => [
                    '*' => ['id', 'url', 'thumb_url', 'name']
                ]
            ]);
    }
}
```

## ⚡ Performance & Optimization

### Image Optimization Job

```php
// app/Jobs/OptimizeImageJob.php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Intervention\Image\Facades\Image;

class OptimizeImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Media $media;

    public function __construct(Media $media)
    {
        $this->media = $media;
    }

    public function handle(): void
    {
        if (!str_starts_with($this->media->mime_type, 'image/')) {
            return;
        }

        $path = $this->media->getPath();
        
        // Optimize the original image
        $image = Image::make($path);
        
        // Compress while maintaining quality
        $image->save($path, 85);
        
        // Update file size in database
        $this->media->update([
            'size' => filesize($path)
        ]);
    }
}
```

### CDN Integration

```php
// config/filesystems.php - CDN disk
'cdn' => [
    'driver' => 's3',
    'key' => env('CDN_ACCESS_KEY_ID'),
    'secret' => env('CDN_SECRET_ACCESS_KEY'),
    'region' => env('CDN_DEFAULT_REGION'),
    'bucket' => env('CDN_BUCKET'),
    'url' => env('CDN_URL'),
    'endpoint' => env('CDN_ENDPOINT'),
    'use_path_style_endpoint' => false,
    'throw' => false,
],
```

## 🔒 Biztonsági Szempontok

### File Validation

```php
// app/Rules/SecureFileUpload.php
<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Http\UploadedFile;

class SecureFileUpload implements Rule
{
    protected array $allowedMimeTypes;
    protected array $blockedExtensions = ['exe', 'bat', 'cmd', 'sh', 'php', 'js'];

    public function __construct(array $allowedMimeTypes = [])
    {
        $this->allowedMimeTypes = $allowedMimeTypes;
    }

    public function passes($attribute, $value): bool
    {
        if (!$value instanceof UploadedFile) {
            return false;
        }

        // Check file extension
        $extension = strtolower($value->getClientOriginalExtension());
        if (in_array($extension, $this->blockedExtensions)) {
            return false;
        }

        // Check MIME type
        if (!empty($this->allowedMimeTypes)) {
            return in_array($value->getMimeType(), $this->allowedMimeTypes);
        }

        return true;
    }

    public function message(): string
    {
        return 'The uploaded file is not secure or allowed.';
    }
}
```

## 📚 További Olvasnivaló

- [Spatie Media Library Documentation](https://spatie.be/docs/laravel-medialibrary)
- [Intervention Image Documentation](http://image.intervention.io/v2)
- [Laravel File Storage](https://laravel.com/docs/filesystem)
- [Filament File Upload](https://filamentphp.com/docs/forms/fields/file-upload)

## 🆘 Troubleshooting

### Gyakori Problémák

**Problem**: Képek nem jelennek meg
```bash
# Ellenőrizd a storage link-et
php artisan storage:link

# Ellenőrizd a file permissions-t
chmod -R 755 storage/
```

**Problem**: Nagy fájlok feltöltési hiba
```ini
# php.ini beállítások
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
```

**Problem**: Memory limit exceeded
```bash
# Queue worker használata nagyobb fájlokhoz
php artisan queue:work
```


