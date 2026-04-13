<?php if (empty($hide_footer)) { ?>
<footer>
    <div class="max-w-[1400px] mx-auto px-[18px] md:px-8 lg:px-10 xl:px-20 mb-6 w-full">
        <div class="rounded-[34px] bg-[#E7EFEC] p-6 md:p-8 shadow-[0_24px_64px_rgba(0,66,65,0.08)]">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                <div class="flex flex-col justify-center rounded-[30px] bg-[#004241] p-6 md:p-8 text-white lg:col-span-7 gap-5">
                    <span class="inline-flex w-fit items-center justify-center rounded-full bg-white/[0.18] px-4 py-2 text-sm font-medium text-white backdrop-blur-[10px] border border-white/[0.14]"><?= htmlspecialchars(__('site.newsletter')) ?></span>
                    <div class="flex flex-col gap-[10px]">
                        <h2 class="max-w-[13ch] font-medium text-white text-3xl sm:text-4xl md:text-2xl lg:text-5xl leading-[0.98]"><?= htmlspecialchars(__('site.newsletter_title')) ?></h2>
                        <p class="max-w-[44ch] text-white/[0.78] text-[17px] leading-[1.4]"><?= htmlspecialchars(__('site.newsletter_text')) ?></p>
                    </div>
                    <form action="<?= htmlspecialchars(route('newsletter.subscribe.web')) ?>" method="post" class="grid grid-cols-1 gap-3 md:grid-cols-[minmax(0,1fr)_220px] lg:grid-cols-[minmax(0,1fr)_184px] xl:grid-cols-[minmax(0,1fr)_220px]">
                        <?= csrf_field() ?>
                        <input type="text" name="company_website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
                        <div class="flex flex-col gap-2">
                            <input
                                type="email"
                                name="newsletter_email"
                                value="<?= htmlspecialchars($newsletterOldEmail) ?>"
                                placeholder="you@example.com"
                                class="h-12 rounded-full border-0 bg-white pl-5 pr-5 text-base text-gray-900 outline-none focus:ring-2 focus:ring-white/30 <?= $newsletterEmailError ? 'ring-2 ring-[#FFD2C9]' : '' ?>"
                                required
                            >
                            <?php if ($newsletterEmailError) { ?>
                            <p class="pl-4 text-sm text-[#FFD2C9]"><?= htmlspecialchars($newsletterEmailError) ?></p>
                            <?php } ?>
                        </div>
                        <button type="submit" class="inline-flex h-12 items-center justify-center rounded-full bg-[#FFF0B6] px-8 font-semibold text-[#004241] transition-colors duration-200 hover:bg-[#FBE9A3] lg:px-6 xl:px-8">
                            <?= htmlspecialchars(__('site.subscribe')) ?>
                        </button>
                    </form>
                </div>

                <div class="flex flex-col rounded-[30px] bg-white p-6 md:p-8 lg:col-span-5 gap-[18px]">
                    <div class="grid grid-cols-2 gap-x-8 gap-y-6">
                        <nav class="flex flex-col gap-[14px]" aria-label="Découvrir">
                            <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#EBF1EF] px-[14px] py-[7px] text-sm font-medium text-[#004241]"><?= htmlspecialchars(__('site.discover')) ?></span>
                            <ul class="m-0 flex list-none flex-col gap-3 p-0">
                                <li><a href="/" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]"><?= htmlspecialchars(__('site.home')) ?></a></li>
                                <li><a href="/a-propos" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]"><?= htmlspecialchars(__('site.about')) ?></a></li>
                                <li><a href="/contact" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]"><?= htmlspecialchars(__('site.contact')) ?></a></li>
                                <li><a href="/faq" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]"><?= htmlspecialchars(__('site.faq')) ?></a></li>
                            </ul>
                        </nav>
                        <nav class="flex flex-col gap-[14px]" aria-label="Légal">
                            <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#EBF1EF] px-[14px] py-[7px] text-sm font-medium text-[#004241]"><?= htmlspecialchars(__('site.legal')) ?></span>
                            <ul class="m-0 flex list-none flex-col gap-3 p-0">
                                <li><a href="/mentions-legales" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]"><?= htmlspecialchars(__('site.legal_notice')) ?></a></li>
                                <li><a href="/politique-confidentialite" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]"><?= htmlspecialchars(__('site.privacy')) ?></a></li>
                                <li><a href="/politique-cookies" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]"><?= htmlspecialchars(__('site.cookies')) ?></a></li>
                            </ul>
                        </nav>
                        <nav class="col-span-2 flex flex-col gap-[14px]" aria-label="<?= htmlspecialchars(__('site.sections')) ?>">
                            <span class="inline-flex w-fit items-center justify-center rounded-full bg-[#EBF1EF] px-[14px] py-[7px] text-sm font-medium text-[#004241]"><?= htmlspecialchars(__('site.sections')) ?></span>
                            <ul class="m-0 grid list-none grid-cols-2 gap-x-6 gap-y-3 p-0">
                                <?php foreach ($categories as $cat) { ?>
                                <li><a href="/categories/<?= htmlspecialchars($cat['slug']) ?>" class="text-base text-[#004241]/85 no-underline transition-colors duration-200 hover:text-[#004241]"><?= htmlspecialchars($cat['name']) ?></a></li>
                                <?php } ?>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 border-t border-[#004241]/10 pt-5 text-sm text-[#004241]/60 md:flex-row md:items-center md:justify-between">
                <p class="m-0">© <?= date('Y') ?> Vivat. <?= htmlspecialchars(__('site.copyright')) ?></p>
                <a href="/contact" class="text-sm text-[#004241]/70 no-underline transition-colors duration-200 hover:text-[#004241]"><?= htmlspecialchars(__('site.question_contact')) ?></a>
            </div>
        </div>
    </div>
</footer>
<?php } ?>
