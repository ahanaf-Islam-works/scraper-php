<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostingResource;
use App\Models\ScrapedPosting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $postings = ScrapedPosting::query()
            ->when($request->query('provider'), fn (Builder $q, $provider) => $q->where('provider', $provider))
            ->latest('id')
            ->paginate(15);

        return PostingResource::collection($postings);
    }
}
