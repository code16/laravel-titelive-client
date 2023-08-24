<?php

namespace Code16\LaravelTiteliveClient;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use JsonSerializable;

class Book extends Model implements JsonSerializable
{
    use HasFactory;

    public static int $AVAILABLE_ON_DEMAND = 1; // Sur commande

    public static int $FORTHCOMING = 2; // À paraître

    public static int $REPRINT = 3; // En réimpression

    public static int $UNAVAILABLE = 4; // Indisponible

    public static int $COMMERCIAL_CHANGE = 5; // Changement de distributeur

    public static int $OUT_OF_PRINT = 6; // Épuisé

    public static int $MISSING = 7; // Manque sans date

    public static int $TO_BE_PUBLISHED_AGAIN = 8; // À reparaître

    protected $guarded = [];

    protected $casts = [
        'published_date' => 'date',
        'id' => 'string',
    ];

    public function getUrlAttribute(): string
    {
        return route('book.show', [
            'id' => $this->id,
            'slug' => Str::slug($this->title),
        ]);
    }

    public function getAvailabilityLabelAttribute(): ?string
    {
        if ($this->hasStock() && $this->availability != self::$FORTHCOMING) {
            return null;
        }

        switch ($this->availability) {
            case self::$AVAILABLE_ON_DEMAND:
                return 'Sur commande';
            case self::$FORTHCOMING:
                return 'À paraître';
            case self::$OUT_OF_PRINT:
                return 'Épuisé';
            case self::$REPRINT:
            case self::$TO_BE_PUBLISHED_AGAIN:
                return 'À reparaître';
            default:
                return 'Indisponible à la vente';
        }
    }

    public function getShortDetailsAttribute(): string
    {
        return collect([
            collect($this->authors)->join(', '),
            $this->editor,
        ])
            ->filter()
            ->join(' — ');
    }

    public function hasStock(): bool
    {
        return $this->stock > 0;
    }

    public function canBeOrdered(): bool
    {
        if (config('maktaba.shopping_closed')) {
            return false;
        }

        return $this->availability != self::$FORTHCOMING
            && ($this->hasStock() || $this->availability == self::$AVAILABLE_ON_DEMAND);
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
