<?php
/** @noinspection all */

namespace Code16\LaravelTiteliveClient\Models {

    use Carbon\Carbon;
    use Code16\LaravelTiteliveClient\Enum\BookAvailability;
    use Illuminate\Database\Eloquent\Model;

    /**
     * @property string id
     * @property string title
     * @property string[] authors
     * @property string|null translator
     * @property string description
     * @property int weight
     * @property int page_count
     * @property string[] category_codes
     * @property string|null readership
     * @property string editor
     * @property int price
     * @property Carbon|null published_date
     * @property string support
     * @property string[] visuals
     * @property BookAvailability availability
     * @property int stock
     * @property string[] editions
     * @property \DateTimeInterface refreshed_at
     */
    class Book extends Model {}
}
