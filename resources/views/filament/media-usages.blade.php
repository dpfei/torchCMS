{{-- 媒体库「引用情况」弹窗：列出哪些内容用了这个文件，方便删除前判断 --}}
@if ($usages->isEmpty())
    <p style="font-size: .875rem; line-height: 1.7;">
        暂无内容引用这个文件，可以放心删除。
    </p>
@else
    <ul style="margin: 0; padding-left: 1.2rem; font-size: .875rem; line-height: 1.9;">
        @foreach ($usages as $usage)
            <li>
                {{ $usage['type'] }} · {{ $usage['field'] }}：
                @if ($usage['url'])
                    <a href="{{ $usage['url'] }}" style="color: #2563eb; text-decoration: underline;">{{ $usage['title'] }}</a>
                @else
                    {{ $usage['title'] }}
                @endif
            </li>
        @endforeach
    </ul>
@endif
