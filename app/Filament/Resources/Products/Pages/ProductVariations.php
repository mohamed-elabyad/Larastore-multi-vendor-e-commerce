<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class ProductVariations extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected static ?string $navigationLabel = 'Variations';

    protected static ?string $title = 'Variation Types';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ClipboardDocumentList;

    public static function getNavigationLabel(): string
    {
        return 'Product Variations';
    }

    public function form(Schema $schema): Schema
    {
        $types = $this->record->variationTypes;
        $fields = [];
        foreach ($types as $type) {
            $fields[] = Hidden::make('variation_type_'.$type->id.'.id');
            $fields[] = TextInput::make('variation_type_'.$type->id.'.name')
                ->label($type->name)
                ->readOnly();
        }

        return $schema
            ->components([
                Repeater::make('variations')
                    ->collapsible()
                    ->addable(false)
                    ->defaultItems(1)
                    ->schema([
                        Section::make()
                            ->schema($fields)
                            ->columns(2)
                            ->columnSpan(2),
                        TextInput::make('quantity')
                            ->label('Quantity')
                            ->numeric(),
                        TextInput::make('price')
                            ->label('Price')
                            ->numeric(),
                    ])
                    ->columns(2)
                    ->columnSpan(2),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $variations = $this->record->variations->toArray();

        $data['variations'] = $this->mergeCartesianWithExisting(
            $this->record->variationTypes,
            $variations
        );

        return $data;
    }

    private function mergeCartesianWithExisting($variationTypes, $existingData): array
    {
        $defaultQuantity = $this->record->quantity;
        $defaultPrice = $this->record->price;

        $cartesianProduct = $this->cartesianProduct($variationTypes, $defaultQuantity, $defaultPrice);

        $mergedResault = [];

        foreach ($cartesianProduct as $product) {
            // Extract option ids from the product combination as an array
            $optionIds = collect($product)
                ->filter(fn ($value, $key) => str_starts_with($key, 'variation_type_'))
                ->map(fn ($option) => $option['id'])
                ->values()
                ->toArray();

            // finding matching entry in existing data
            $match = array_filter($existingData, function ($existingOption) use ($optionIds) {
                return $existingOption['variation_type_option_ids'] === $optionIds;
            });

            if (! empty($match)) {
                $existingEntry = reset($match);
                $product['id'] = $existingEntry['id'];
                $product['quantity'] = $existingEntry['quantity'];
                $product['price'] = $existingEntry['price'];
            } else {
                // set default quantity and price if no match
                $product['id'] = null;
                $product['quantity'] = $defaultQuantity;
                $product['price'] = $defaultPrice;
            }

            $mergedResault[] = $product;
        }

        return $mergedResault;
    }

    private function cartesianProduct($variationTypes, $defaultQuantity = null, $defaultPrice = null): array
    {
        $result = [[]];

        foreach ($variationTypes as $index => $variationType) {
            $temp = [];

            foreach ($variationType->options as $option) {
                // add the current option to all existing compenations
                foreach ($result as $combination) {
                    $newCombination = $combination + [
                        'variation_type_'.($variationType->id) => [
                            'id' => $option->id,
                            'name' => $option->name,
                            'type' => $variationType->name,
                        ],
                    ];

                    $temp[] = $newCombination;
                }
            }

            $result = $temp;
        }

        // add quantity and price to combination
        foreach ($result as &$combination) {
            if (count($combination) === count($variationTypes)) {
                $combination['quantity'] = $defaultQuantity;
                $combination['price'] = $defaultPrice;
            }
        }

        return $result;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // dd($data);

        $formatedData = [];

        foreach ($data['variations'] as $option) {
            $variationTypeOptionIds = [];
            foreach ($this->record->variationTypes as $i => $variationType) {
                $variationTypeOptionIds[] = $option['variation_type_'.($variationType->id)]['id'];
            }

            $quantity = $option['quantity'];
            $price = $option['price'];

            if ($option['id'] ?? null) {
                $formatedData[] = [
                    'id' => $option['id'],
                    'variation_type_option_ids' => $variationTypeOptionIds,
                    'quantity' => $quantity,
                    'price' => $price,
                ];
            } else {
                $formatedData[] = [
                    'variation_type_option_ids' => $variationTypeOptionIds,
                    'quantity' => $quantity,
                    'price' => $price,
                ];
            }
        }

        $data['variations'] = $formatedData;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $variations = $data['variations'];
        unset($data['variations']);

        $variations = collect($variations)
            ->map(function ($variation) {
                if ($variation['id'] ?? null) {
                    return [
                        'id' => $variation['id'],
                        'variation_type_option_ids' => json_encode($variation['variation_type_option_ids']),
                        'quantity' => $variation['quantity'],
                        'price' => $variation['price'],
                    ];
                } else {
                    return [
                        'variation_type_option_ids' => json_encode($variation['variation_type_option_ids']),
                        'quantity' => $variation['quantity'],
                        'price' => $variation['price'],
                    ];
                }
            })
            ->toArray();

        $record->variations()->upsert($variations, ['id'], [
            'variation_type_option_ids',
            'quantity',
            'price',
        ]);

        return $record;
    }
}
