@if($newsfeatured)
<a href="{{ $newsfeatured->url }}" target="_blank"
   class="group block bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition mb-10">
    <div class="grid grid-cols-1 lg:grid-cols-2">
        @if($newsfeatured->image)
        <img src="{{ $newsfeatured->image }}" alt="{{ $newsfeatured->title }}"
             class="w-full h-72 object-cover group-hover:scale-105 transition-transform duration-500" />
        @else
        <div class="w-full h-72 bg-green-50 flex items-center justify-center">
            <x-heroicon-o-newspaper class="h-16 w-16 text-green-200" />
        </div>
        @endif
        <div class="p-8 flex flex-col justify-center gap-4">
            <div class="flex items-center gap-2 flex-wrap">
                <span class="bg-green-50 text-green-700 text-xs font-bold px-3 py-1 rounded-full">@lang('blog::app.news.featured_badge')</span>
                <span class="bg-gray-100 text-gray-500 text-xs font-bold px-3 py-1 rounded-full">{{ $newsfeatured->source }}</span>
            </div>
            <h2 class="text-2xl font-black text-gray-900 leading-tight group-hover:text-green-600 transition">{{ $newsfeatured->title }}</h2>
            <p class="text-gray-500 text-sm leading-relaxed line-clamp-3">{{ $newsfeatured->description }}</p>
            <div class="flex items-center justify-between">
                <span class="text-xs text-gray-400">{{ $newsfeatured->published_at?->diffForHumans() }}</span>
                <span class="text-green-500 text-sm font-bold group-hover:underline">@lang('blog::app.news.read_more')</span>
            </div>
        </div>
    </div>
</a>
@endif

@if($newsArticles->count())
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($newsArticles as $article)
    <a href="{{ $article->url }}" target="_blank"
       class="group bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition flex flex-col">
        @if($article->image)
        <div class="overflow-hidden">
            <img src="{{ $article->image }}" alt="{{ $article->title }}"
                 class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500" />
        </div>
        @else
        <div class="w-full h-48 bg-green-50 flex items-center justify-center">
            <x-heroicon-o-newspaper class="h-10 w-10 text-green-200" />
        </div>
        @endif
        <div class="p-5 flex flex-col gap-3 flex-1">
            <div class="flex items-center gap-2">
                <span class="bg-gray-100 text-gray-500 text-xs font-bold px-2.5 py-1 rounded-full">{{ $article->source }}</span>
                <span class="text-xs text-gray-400">{{ $article->published_at?->diffForHumans() }}</span>
            </div>
            <h3 class="font-black text-gray-800 text-sm leading-snug line-clamp-2 group-hover:text-green-600 transition">{{ $article->title }}</h3>
            <p class="text-gray-400 text-xs leading-relaxed line-clamp-2 flex-1">{{ $article->description }}</p>
            <span class="text-green-500 text-xs font-bold group-hover:underline">@lang('blog::app.news.read_more')</span>
        </div>
    </a>
    @endforeach
</div>
@else
<div class="py-20">
    @include('core::partials.empty-state', ['preset' => 'articles'])
</div>
@endif
