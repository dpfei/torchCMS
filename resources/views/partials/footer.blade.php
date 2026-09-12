<footer class="mt-16 border-t border-gray-100 bg-white">
    <div class="mx-auto max-w-6xl px-4 py-10">
        <div class="grid gap-8 md:grid-cols-3">
            <div>
                <h3 class="text-base font-semibold text-gray-900">{{ setting('site_name', 'torchCMS') }}</h3>
                <p class="mt-3 text-sm leading-relaxed text-gray-500">
                    {{ setting('site_description') }}
                </p>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-900">网站导航</h3>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    @foreach ($menu ?? [] as $item)
                        <li>
                            <a href="{{ $item->link }}"
                               @if ($item->target === '_blank') target="_blank" rel="noopener" @endif
                               class="transition hover:text-blue-600">
                                {{ $item->label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div>
                <h3 class="text-sm font-semibold text-gray-900">联系我们</h3>
                <ul class="mt-3 space-y-2 text-sm text-gray-500">
                    @if (setting('contact_email'))
                        <li>邮箱：{{ setting('contact_email') }}</li>
                    @endif
                    @if (setting('contact_phone'))
                        <li>电话：{{ setting('contact_phone') }}</li>
                    @endif
                    @if (setting('icp'))
                        <li>
                            <a href="https://beian.miit.gov.cn" target="_blank" rel="noopener" class="transition hover:text-blue-600">
                                {{ setting('icp') }}
                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        </div>

        <div class="mt-8 border-t border-gray-100 pt-6 text-center text-xs text-gray-400">
            {{ setting('copyright', '© ' . date('Y') . ' torchCMS') }}
        </div>
    </div>
</footer>
