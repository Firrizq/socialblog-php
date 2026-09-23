</main>

            <!-- TAHAP 3: Right Sidebar -->
            <aside class="hidden xl:block w-80 sticky top-16 h-[calc(100vh-4rem)] p-gutter border-l border-outline-variant/30 overflow-y-auto">
                <div class="flex flex-col gap-space-lg">
                    <div class="relative flex items-center">
                        <span class="material-symbols-outlined absolute left-space-md text-outline text-lg">search</span>
                        <input class="w-full pl-10 pr-space-md py-space-xs bg-surface-container-lowest border border-outline-variant/40 rounded-full font-body-md text-on-surface placeholder:text-outline focus:outline-none focus:border-primary-container focus:ring-1 focus:ring-primary-container" placeholder="Search EmeraldInk..." type="text"/>
                    </div>
                    
                    <div class="flex flex-col gap-space-md">
                        <span class="font-title-md text-on-surface">Trending Topics</span>
                        <div class="flex flex-wrap gap-space-xs">
                            <a class="px-space-md py-space-xs rounded-full bg-surface-container border border-outline-variant/30 text-on-surface-variant font-caption hover:border-primary hover:text-primary transition-colors" href="#">#PHP Native</a>
                            <a class="px-space-md py-space-xs rounded-full bg-surface-container border border-outline-variant/30 text-on-surface-variant font-caption hover:border-primary hover:text-primary transition-colors" href="#">#WebArchitecture</a>
                            <a class="px-space-md py-space-xs rounded-full bg-surface-container border border-outline-variant/30 text-on-surface-variant font-caption hover:border-primary hover:text-primary transition-colors" href="#">#Minimalism</a>
                        </div>
                    </div>
                    
                    <div class="flex flex-col gap-space-md">
                        <span class="font-title-md text-on-surface">Suggested Writers</span>
                        <div class="flex flex-col gap-space-md">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-space-sm">
                                    <div class="w-8 h-8 rounded-full bg-surface-container-high flex items-center justify-center font-label-md text-primary">AL</div>
                                    <div>
                                        <p class="font-label-md text-on-surface leading-tight">Arthur Lyra</p>
                                        <p class="font-caption text-on-surface-variant">Essays &amp; Systems</p>
                                    </div>
                                </div>
                                <button class="px-space-sm py-space-xs rounded-full bg-surface-container border border-outline-variant text-on-surface hover:border-primary hover:text-primary font-caption transition-colors">Follow</button>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
            <!-- Akhir dari max-w-7xl -->
        </div>
    <!-- Akhir dari md:pl-60 -->
    </div>

    <!-- Quill.js JS Script diletakkan di footer agar editor bisa jalan -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
</body>
</html>