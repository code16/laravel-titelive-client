<?php

namespace Code16\LaravelTiteliveClient;

use Code16\LaravelTiteliveClient\Database\Factories\BookFactory;
use Code16\LaravelTiteliveClient\Enum\BookAvailability;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use JsonSerializable;

class Book extends Model implements JsonSerializable
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
        'published_date' => 'date',
        'availability' => BookAvailability::class,
    ];

    protected static function newFactory()
    {
        return new BookFactory;
    }

    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => route('book.show', [
                'id' => $this->id,
                'slug' => Str::slug($this->title),
            ])
        );
    }

    protected function shortDetails(): Attribute
    {
        return Attribute::make(
            get: fn () => collect([
                collect($this->authors)->join(', '),
                $this->editor,
            ])
                ->filter()
                ->join(' — ')
        );
    }

    public function hasStock(): bool
    {
        return $this->stock > 0;
    }

    public function canBeOrdered(): bool
    {
        if (config('titelive-client.shopping_closed')) {
            return false;
        }

        return $this->availability != BookAvailability::Forthcoming
            && ($this->hasStock() || $this->availability == BookAvailability::AvailableOnDemand);
    }

    public function visual(string $size): string
    {
        $url = $this->visuals[$size];

        if (preg_match('/no_image\.png/', $url)) {
            return asset('/img/book-placeholder.png');
        }

        return $url;
    }

    public function jsonSerialize(): array
    {
        return (new BookJsonResource($this))->jsonSerialize();
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if (is_array($value)) {
            return new static($value);
        }

        return null;
    }
}
