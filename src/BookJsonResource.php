<?php

namespace Code16\LaravelTiteliveClient;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Book
 */
class BookJsonResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'visuals' => [
                'large' => $this->visual('large'),
                'medium' => $this->visual('medium'),
                'thumbnail' => $this->visual('thumbnail'),
            ],
            'formatted_price' => sprintf('%s&nbsp;€', number_format($this->price / 100, 2, ',', '')),
            'formatted_published_date' => $this->published_date
                ? $this->published_date->isoFormat('Do MMMM YYYY')
                : null,
            'availability_label' => $this->availability?->getLabel(),
            'short_details' => $this->short_details,
            'orderable' => $this->canBeOrdered(),
            'url' => $this->url,
            $this->merge(parent::toArray($request)),
        ];
    }
}
