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
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

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
                Step::make('Course Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (callable $set, $state) => $set('slug', Str::slug($state))),
                        TextInput::make('slug')
                            ->required()
                            ->unique(Course::class, 'slug', ignoreRecord: true)
                            ->hidden(),
                        Placeholder::make('thumbnail_preview')
                            ->label('Current Thumbnail')
                            ->content(function ($record) {
                                if ($record && $record->thumbnail_url) {
                                    return new HtmlString('<img src="' . $record->thumbnail_url . '" style="max-width: 200px; height: auto;">');
                                }
                                return 'No thumbnail uploaded.';
                            })
                            ->visibleOn('edit'),
                        FileUpload::make('thumbnail')
                            ->label('Upload New Thumbnail')
                            ->directory('thumbnails')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imagePreviewHeight('100'),
                        Textarea::make('about')
                            ->rows(4),
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->required(),
                        Toggle::make('is_popular')
                            ->label('Popular Course')
                            ->default(false),
                        Repeater::make('benefits')
                            ->relationship()
                            ->schema([
                                TextInput::make('name')->label('Benefit Title')->required(),
                                Textarea::make('description')->rows(2)->required(),
                            ])
                            ->columnSpan('full'),
                        (function () {
                            $user = auth()->user();
                            $mentorsRepeater = Repeater::make('mentors')
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
                                    TextInput::make('job')->label('Job Title'),
                                    Textarea::make('about')->label('About Mentor'),
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
                Step::make('Course Structure & Quizzes')
                    ->schema([
                        Repeater::make('sections')
                            ->relationship()
                            ->orderable('position')
                            ->schema([
                                TextInput::make('name')->required(),
                                Repeater::make('contents')
                                    ->relationship()
                                    ->orderable('position')
                                    ->schema([
                                        TextInput::make('name')->required(),
                                        RichEditor::make('content'),
                                        Toggle::make('has_video')
                                            ->label('Include a video for this content?')
                                            ->live(),
                                        Group::make()
                                            ->relationship('video')
                                            ->schema([
                                                TextInput::make('id_youtube')
                                                    ->label('YouTube Video ID')
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
                                                    ->label('Video Preview')
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
                                            ->label('Include an attachment for this content?')
                                            ->live(),
                                        Group::make()
                                            ->relationship('attachment')
                                            ->schema([
                                                FileUpload::make('file')
                                                    ->label('Attachment File')
                                                    ->directory('course-attachments')
                                                    ->disk('public')
                                                    ->required(fn ($record) => !$record?->file)
                                                    ->downloadable(),
                                            ])
                                            ->visible(fn(Forms\Get $get) => $get('has_attachment')),
                                        Toggle::make('has_quiz')
                                            ->label('Include a quiz for this content?')
                                            ->live(),
                                        Group::make()
                                            ->relationship('quiz')
                                            ->schema([
                                                TextInput::make('title')->required(),
                                                Repeater::make('questions')
                                                    ->relationship()
                                                    ->schema([
                                                        Textarea::make('question_text')->required(),
                                                        Repeater::make('options')
                                                            ->relationship()
                                                            ->schema([
                                                                TextInput::make('option_text')->required(),
                                                                Toggle::make('is_correct')->default(false),
                                                            ])
                                                            ->rules([
                                                                function () {
                                                                    return function (string $attribute, $value, \Closure $fail) {
                                                                        $correctCount = collect($value)->where('is_correct', true)->count();
                                                                        if ($correctCount > 1) {
                                                                            $fail('You may only have one correct answer per question.');
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
                Step::make('Pricing & Batches')
                    ->schema(function () {
                        $user = auth()->user();

                        // Define the base select for batches repeater
                        $pricingSelectInRepeater = Select::make('pricing_id')
                            ->label('Pricing')
                            ->options(Pricing::all()->pluck('name', 'id'))
                            ->required();
                        
                        // Define the base select for on-demand
                        $pricingsSelect = Select::make('pricings')
                            ->multiple()
                            ->relationship('pricings', 'name')
                            ->options(Pricing::all()->pluck('name', 'id'))
                            ->visible(fn(Forms\Get $get) => $get('course_type') === 'on_demand');

                        // If user is admin, add the createOptionForm
                        if ($user->hasRole('admin')) {
                            $pricingSelectInRepeater->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('price')->numeric()->required(),
                                TextInput::make('duration')->numeric()->required()->label('Duration (in days)'),
                            ]);
                            $pricingsSelect->createOptionForm([
                                TextInput::make('name')->required(),
                                TextInput::make('price')->numeric()->required(),
                                TextInput::make('duration')->numeric()->required()->label('Duration (in days)'),
                            ]);
                        }

                        return [
                            Radio::make('course_type')
                                ->label('Course Type')
                                ->options([
                                    'batch' => 'Batch Based',
                                    'on_demand' => 'On Demand',
                                ])
                                ->live(),
                            Repeater::make('batches')
                                ->relationship()
                                ->schema([
                                    TextInput::make('name')->required(),
                                    Select::make('mentor_id')
                                        ->label('Mentor for this Batch')
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
                                    TextInput::make('quota')->numeric()->required(),
                                    DatePicker::make('start_date')->required(),
                                    DatePicker::make('end_date')->required(),
                                    $pricingSelectInRepeater,
                                ])
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
                    ->getStateUsing(fn(Course $record) => $record->thumbnail_url)
                    ->square(),
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Category'),
                Tables\Columns\IconColumn::make('is_popular')->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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
