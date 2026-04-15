<div id="mobile-menu-panel"
     data-open="false"
     class="absolute top-full left-[18px] right-[18px] z-50 mt-0 origin-top isolate overflow-hidden rounded-[34px] border border-white/10 bg-[#004241] shadow-[0_20px_60px_rgba(0,40,38,0.35)] transition-[clip-path,opacity,max-height,visibility] duration-[650ms] ease-[cubic-bezier(0.22,1,0.36,1)] md:left-8 md:right-4 lg:left-10 lg:right-[63px] lg:mt-0 xl:left-20 xl:right-[79px] data-[open=false]:pointer-events-none data-[open=false]:invisible data-[open=false]:max-h-0 data-[open=false]:overflow-hidden data-[open=false]:opacity-0 data-[open=true]:pointer-events-auto data-[open=true]:visible data-[open=true]:max-h-[min(88vh,960px)] data-[open=true]:overflow-y-auto data-[open=true]:overflow-x-hidden data-[open=true]:opacity-100"
     role="dialog" aria-label="Menu de navigation" aria-modal="true">
    <div class="p-8 md:p-10 lg:p-12">

    <?php if (auth()->check()) { ?>
    <div class="mb-7">
        <a href="<?= auth()->user()->hasRole(['contributor', 'admin']) ? url('/contributor/dashboard') : url('/') ?>" class="flex items-center gap-4 rounded-[20px] border border-white/[0.08] bg-white/8 p-5 no-underline transition-all duration-200 hover:border-white/15 hover:bg-white/15">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-white/15 text-base font-semibold text-white">
                <?= strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) ?>
            </div>
            <div class="min-w-0 flex-1">
                <p class="truncate text-base font-semibold text-white"><?= htmlspecialchars(auth()->user()->name ?? __('site.my_account')) ?></p>
                <p class="text-sm text-white/55"><?= auth()->user()->hasRole(['contributor', 'admin']) ? __('site.writer_space') : __('site.my_profile') ?></p>
            </div>
            <svg class="h-4 w-4 shrink-0 text-white/35" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5l7 7-7 7"/></svg>
        </a>
        <form action="<?= url('/logout') ?>" method="post" class="mt-2 flex justify-end pr-1">
            <?= csrf_field() ?>
            <button type="submit" class="flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs text-white/40 transition-colors duration-200 hover:bg-white/8 hover:text-white/70">
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <?= htmlspecialchars(__('site.logout')) ?>
            </button>
        </form>
    </div>
    <?php } ?>

    <div class="grid grid-cols-1 gap-8 lg:grid-cols-[220px_minmax(0,1fr)] lg:gap-10">
        <nav aria-label="Navigation principale">
            <p class="mb-2 px-1 text-[11px] font-semibold uppercase tracking-[0.13em] text-white/35">Menu</p>
            <div class="flex flex-col gap-0.5">
                <a href="/" class="rounded-[14px] px-4 py-3 text-[17px] font-normal text-white/90 no-underline transition-colors duration-200 hover:bg-white/10 hover:text-white"><?= htmlspecialchars(__('site.home')) ?></a>
                <a href="/a-propos" class="rounded-[14px] px-4 py-3 text-[17px] font-normal text-white/90 no-underline transition-colors duration-200 hover:bg-white/10 hover:text-white"><?= htmlspecialchars(__('site.about')) ?></a>
                <a href="/contact" class="rounded-[14px] px-4 py-3 text-[17px] font-normal text-white/90 no-underline transition-colors duration-200 hover:bg-white/10 hover:text-white"><?= htmlspecialchars(__('site.contact')) ?></a>
                <a href="/faq" class="rounded-[14px] px-4 py-3 text-[17px] font-normal text-white/90 no-underline transition-colors duration-200 hover:bg-white/10 hover:text-white"><?= htmlspecialchars(__('site.faq')) ?></a>
                <div class="mt-3 flex flex-col gap-2">
                    <?php if (! auth()->check()) { ?>
                    <a href="<?= url('/login') ?>"
                       class="flex items-center gap-3 rounded-[14px] bg-[#FFF1B9]/12 px-4 py-3 text-[17px] font-medium text-[#FFF1B9] no-underline transition-colors duration-200 hover:bg-[#FFF1B9]/22">
                        <svg class="h-4 w-4 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m-6-3h11.25m0 0L18 8.75m3.25 3.25L18 15.25"/></svg>
                        Se connecter
                    </a>
                    <?php } ?>

                    <a href="<?= auth()->check() && auth()->user()->hasRole(['contributor', 'admin']) ? url('/contributor/dashboard') : config('vivat.writer_signup_url', '/register') ?>"
                       class="flex items-center gap-3 rounded-[14px] bg-[#FFF1B9]/12 px-4 py-3 text-[17px] font-medium text-[#FFF1B9] no-underline transition-colors duration-200 hover:bg-[#FFF1B9]/22">
                        <svg class="h-4 w-4 shrink-0 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <?= htmlspecialchars(__('site.write_article')) ?>
                    </a>
                </div>
            </div>
        </nav>

        <div>
            <p class="mb-2 px-1 pt-7 text-[11px] font-semibold uppercase tracking-[0.13em] text-white/35 lg:pt-0"><?= htmlspecialchars(__('site.sections')) ?></p>
            <nav class="grid grid-cols-2 gap-x-2 gap-y-1.5 sm:grid-cols-3" aria-label="<?= htmlspecialchars(__('site.sections')) ?>">
                <?php foreach ($categories as $cat) { ?>
                <a href="/categories/<?= htmlspecialchars($cat['slug']) ?>" class="rounded-[14px] px-4 py-3.5 text-[17px] font-medium leading-[1.35] text-white/80 no-underline transition-colors duration-200 hover:bg-white/10 hover:text-white"><?= htmlspecialchars($cat['name']) ?></a>
                <?php } ?>
            </nav>
        </div>
    </div>
    </div>
</div>
