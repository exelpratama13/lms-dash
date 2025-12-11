<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Models\Course;
use App\Models\Category;
use App\Models\User;
use App\Models\Pricing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Group;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\HtmlString;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;
    protected static ?string $navigationGroup = 'Manajemen Kursus';

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        if ($user->hasRole('admin')) {
            return parent::getEloquentQuery();
        } elseif ($user->hasRole('mentor')) {
            $mentorCourseIds = $user->courseMentors()->pluck('course_id');
            return parent::getEloquentQuery()->whereIn('id', $mentorCourseIds);
        }

        return parent::getEloquentQuery()->whereRaw('1 = 0'); // Return no courses for other roles
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Wizard::make([
                Step::make('Informasi Kursus')
                    ->columns(2)
                    ->schema([
                        // Left Column
                        Group::make()
                            ->schema([
                                Placeholder::make('custom-repeater-styles')
                                    ->label(false)
                                    ->content(new HtmlString('<style>.sections-repeater > div.justify-center, .contents-repeater > div.justify-center { justify-content: flex-start !important; }</style>')),
                                TextInput::make('name')
                                    ->label('Nama Kursus')
                                    ->prefixIcon('heroicon-o-academic-cap')
                                    ->helperText('Tulis judul utama untuk kursus ini.')
                                    ->required()
                                    ->maxLength(255)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(fn (callable $set, $state) => $set('slug', Str::slug($state))),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(Course::class, 'slug', ignoreRecord: true)
                                    ->hidden(),
                                Textarea::make('about')
                                    ->label('Tentang Kursus')
                                    ->rows(4),
                                Select::make('category_id')
                                    ->label('Kategori')
                                    ->relationship('category', 'name')
                                    ->required(),
                                Toggle::make('is_popular')
                                    ->label('Kursus Populer')
                                    ->default(false),
                            ])->columnSpan(1),

                        // Right Column
                        Group::make()
                            ->schema([
                                Placeholder::make('thumbnail_preview')
                                    ->label('Pratinjau Thumbnail')
                                    ->content(function ($record) {
                                        if ($record && $record->thumbnail_url) {
                                            return new HtmlString('<img src="' . $record->thumbnail_url . '" style="max-width: 200px; height: auto;">');
                                        }
                                        return 'Tidak ada thumbnail.';
                                    })
                                    ->visibleOn('edit'),
                                FileUpload::make('thumbnail')
                                    ->label('Unggah Thumbnail Baru')
                                    ->directory('thumbnails')
                                    ->image()
                                    ->imageResizeMode('cover')
                                    ->imagePreviewHeight('100'),
                            ])->columnSpan(1),

                        // Full-width components at the bottom
                        Repeater::make('benefits')
                            ->label('Manfaat')
                            ->relationship()
                            ->addActionLabel('Tambahkan Manfaat')
                            ->schema([
                                TextInput::make('name')->label('Judul Manfaat')->required(),
                                Textarea::make('description')->label('Deskripsi Manfaat')->rows(2)->required(),
                            ])
                            ->minItems(1)
                            ->collapsible()
                            ->itemLabel(fn (array $state) => $state['name'] ?? 'Manfaat Baru')
                            ->columnSpan('full'),
                        (function () {
                            $user = auth()->user();
                            $mentorsRepeater = Repeater::make('mentors')
                                ->collapsible()
                                ->relationship()
                                ->schema([
                                    Select::make('user_id')
                                        ->label('Mentor')
                                        ->options(function () use ($user) {
                                            if ($user->hasRole('admin')) {
                                                return User::role('mentor')->pluck('name', 'id');
                                            }
                                            return User::where('id', $user->id)->pluck('name', 'id');
                                        })
                                        ->default(fn () => $user->hasRole('mentor') ? $user->id : null)
                                        ->disabled($user->hasRole('mentor'))
                                        ->required(),
                                    TextInput::make('job')->label('Jabatan'),
                                    Textarea::make('about')->label('Tentang Mentor'),
                                ])
                                ->required();

                            if ($user->hasRole('mentor')) {
                                return $mentorsRepeater
                                    ->default([['user_id' => $user->id]])
                                    ->minItems(1)
                                    ->maxItems(1)
                                    ->disableItemCreation()
                                    ->disableItemDeletion();
                            }

                            return $mentorsRepeater;
                        })(),
                    ]),
                Step::make('Struktur Kursus & Kuis')
                    ->schema([
                        Repeater::make('sections')
                            ->addAction(fn (Action $action) => $action
                                ->label('Tambah Bagian Baru')
                                ->icon('heroicon-o-plus-circle')
                                ->color('success')
                            )
                            ->relationship()
                            ->orderable('position')
                            ->collapsible()
                            ->minItems(1)
                            ->itemLabel(fn (array $state) => $state['name'] ?? 'Bagian Baru')
                            ->extraAttributes(['class' => 'sections-repeater'])
                            ->schema([
                                TextInput::make('name')->label('Nama Bagian')->required(),
                                Repeater::make('contents')
                                    ->addAction(fn (Action $action) => $action
                                        ->label('Tambah Konten Baru')
                                        ->icon('heroicon-o-plus')
                                        ->color('info')
                                    )
                                    ->relationship()
                                    ->orderable('position')
                                    ->collapsible()
                                    ->minItems(1)
                                    ->itemLabel(fn (array $state) => $state['name'] ?? 'Konten Baru')
                                    ->extraAttributes(['class' => 'contents-repeater'])
                                    ->schema([
                                        TextInput::make('name')->label('Nama Konten')->required(),
                                        RichEditor::make('content')->label('Konten Detail'),
                                        Toggle::make('has_video')
                                            ->label('Sertakan video untuk konten ini?')
                                            ->live()
                                            ->afterStateHydrated(function (Toggle $component, ?\Illuminate\Database\Eloquent\Model $record) {
                                                if ($record) {
                                                    $component->state(!is_null($record->video));
                                                }
                                            }),
                                        Group::make()
                                            ->relationship('video')
                                            ->schema([
                                                TextInput::make('id_youtube')
                                                    ->label('ID Video YouTube')
                                                    ->live()
                                                    ->afterStateUpdated(function (callable $set, ?string $state) {
                                                        // Regex to extract YouTube video ID from various URL formats
                                                        $pattern = '/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/';
                                                        if (preg_match($pattern, $state, $matches)) {
                                                            $set('id_youtube', $matches[1]);
                                                        }
                                                    })
                                                    ->required(),
                                                Placeholder::make('video_preview')
                                                    ->label('Pratinjau Video')
                                                    ->content(function (callable $get): ?HtmlString {
                                                        $youtubeId = $get('id_youtube');
                                                        if (is_string($youtubeId) && !empty($youtubeId)) {
                                                            return new HtmlString(
                                                                '<iframe width="100%" height="315" src="https://www.youtube.com/embed/' . e($youtubeId) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>'
                                                            );
                                                        }
                                                        return null;
                                                    })
                                                    ->visible(fn(callable $get) => !empty($get('id_youtube'))),
                                            ])
                                            ->visible(fn(Forms\Get $get) => $get('has_video')),
                                        Toggle::make('has_attachment')
                                            ->label('Sertakan lampiran untuk konten ini?')
                                            ->live()
                                            ->afterStateHydrated(function (Toggle $component, ?\Illuminate\Database\Eloquent\Model $record) {
                                                if ($record) {
                                                    $component->state(!is_null($record->attachment));
                                                }
                                            }),
                                        Group::make()
                                            ->relationship('attachment')
                                            ->schema([
                                                FileUpload::make('file')
                                                    ->label('File Lampiran')
                                                    ->directory('course-attachments')
                                                    ->disk('public')
                                                    ->required(fn ($record) => !$record?->file)
                                                    ->downloadable(),
                                            ])
                                            ->visible(fn(Forms\Get $get) => $get('has_attachment')),
                                        Toggle::make('has_quiz')
                                            ->label('Sertakan kuis untuk konten ini?')
                                            ->live()
                                            ->afterStateHydrated(function (Toggle $component, ?\Illuminate\Database\Eloquent\Model $record) {
                                                if ($record) {
                                                    $component->state(!is_null($record->quiz));
                                                }
                                            }),
                                        Group::make()
                                            ->relationship('quiz')
                                            ->schema([
                                                TextInput::make('title')->label('Judul Kuis')->required(),
                                                Repeater::make('questions')
                                                    ->relationship()
                                                    ->addActionLabel('Tambah Pertanyaan')
                                                    ->collapsible()
                                                    ->minItems(1)
                                                    ->itemLabel(fn (array $state) => $state['question_text'] ?? 'Pertanyaan Baru')
                                                    ->schema([
                                                        Textarea::make('question_text')->label('Teks Pertanyaan')->required(),
                                                        Repeater::make('options')
                                                            ->relationship()
                                                            ->addActionLabel('Tambah Opsi')
                                                            ->collapsible()
                                                            ->minItems(1)
                                                            ->schema([
                                                                TextInput::make('option_text')->label('Teks Opsi')->required(),
                                                                Toggle::make('is_correct')->label('Tandai sebagai Jawaban Benar')->default(false),
                                                            ])
                                                            ->rules([
                                                                function () {
                                                                    return function (string $attribute, $value, \Closure $fail) {
                                                                        $correctCount = collect($value)->where('is_correct', true)->count();
                                                                        if ($correctCount > 1) {
                                                                            $fail('Hanya boleh ada satu jawaban yang benar untuk setiap pertanyaan.');
                                                                        }
                                                                    };
                                                                }
                                                            ])
                                                            ->columnSpan('full'),
                                                    ])->columnSpan('full')
                                            ])
                                            ->visible(fn(Forms\Get $get) => $get('has_quiz')),
                                    ])->columnSpan('full')
                            ])->columnSpan('full')
                    ]),
                Step::make('Harga & Batch')
                    ->schema(function () {
                        $user = auth()->user();

                        // Define the base select for batches repeater
                        $pricingSelectInRepeater = Select::make('pricing_id')
                            ->label('Harga')
                            ->options(Pricing::all()->pluck('name', 'id'))
                            ->required();
                        
                        // Define the base select for on-demand
                        $pricingsSelect = Select::make('pricings')
                            ->label('Pilihan Harga (On Demand)')
                            ->multiple()
                            ->relationship('pricings', 'name')
                            ->options(Pricing::all()->pluck('name', 'id'))
                            ->visible(fn(Forms\Get $get) => $get('course_type') === 'on_demand');

                        // If user is admin, add the createOptionForm
                        if ($user->hasRole('admin')) {
                            $pricingSelectInRepeater->createOptionForm([
                                TextInput::make('name')->label('Nama Opsi Harga')->required(),
                                TextInput::make('price')->label('Harga')->numeric()->required(),
                                TextInput::make('duration')->label('Durasi (dalam hari)')->numeric()->required(),
                            ]);
                            $pricingsSelect->createOptionForm([
                                TextInput::make('name')->label('Nama Opsi Harga')->required(),
                                TextInput::make('price')->label('Harga')->numeric()->required(),
                                TextInput::make('duration')->label('Durasi (dalam hari)')->numeric()->required(),
                            ]);
                        }

                        return [
                            Radio::make('course_type')
                                ->label('Tipe Kursus')
                                ->options([
                                    'batch' => 'Per Batch',
                                    'on_demand' => 'On Demand',
                                ])
                                ->live(),
                            Repeater::make('batches')
                                ->relationship()
                                ->schema([
                                    TextInput::make('name')->label('Nama Batch')->required(),
                                    Select::make('mentor_id')
                                        ->label('Mentor untuk Batch Ini')
                                        ->options(function (callable $get) {
                                            $mentorData = $get('../../mentors');
                                            if (empty($mentorData)) {
                                                return [];
                                            }
                                            $mentorIds = array_column($mentorData, 'user_id');
                                            return User::whereIn('id', $mentorIds)->pluck('name', 'id');
                                        })
                                        ->default(function(callable $get) {
                                            $mentorData = $get('../../mentors');
                                            return $mentorData[0]['user_id'] ?? null;
                                        })
                                        ->required(),
                                    TextInput::make('quota')->label('Kuota')->numeric()->required(),
                                    DatePicker::make('start_date')->label('Tanggal Mulai')->required(),
                                    DatePicker::make('end_date')->label('Tanggal Selesai')->after('start_date')->required(),
                                    $pricingSelectInRepeater,
                                ])
                                ->minItems(1)
                                ->collapsible()
                                ->visible(fn(Forms\Get $get) => $get('course_type') === 'batch')
                                ->columnSpan('full'),
                            $pricingsSelect,
                        ];
                    }),
            ])->columnSpan('full'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Thumbnail')
                    ->getStateUsing(fn(Course $record) => $record->thumbnail_url)
                    ->square(),
                Tables\Columns\TextColumn::make('name')->label('Nama Kursus')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Kategori'),
                Tables\Columns\IconColumn::make('is_popular')->label('Populer')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->label('Kategori')->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
->paginationPageOptions([5, 10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            // bisa ditambahkan RelationManagers misalnya: CourseSectionsRelationManager, CourseBenefitRelationManager
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}

