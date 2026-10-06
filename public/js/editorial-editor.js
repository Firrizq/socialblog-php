/**
 * Editorial WYSIWYG Editor
 * A bespoke, lightweight, and fully customizable rich text editor
 * built for Blogggle's warm independent press aesthetic.
 * 
 * Features:
 * - Zero external dependencies (No Quill, No Prosemirror)
 * - 100% Tailwind CSS design tokens
 * - Sticky top toolbar + Floating selection bubble toolbar
 * - Live Markdown shortcuts (#, ##, ###, >, -, 1., ```, ---)
 * - Keyboard shortcuts (Cmd/Ctrl + B, I, U, K, Z, Y)
 * - Drag-and-drop & clipboard image/video uploads
 * - Auto-drafting & word count / reading time calculation
 * - Clean semantic HTML output
 */

(function (window) {
    'use strict';

    class EditorialEditor {
        constructor(options = {}) {
            this.container = typeof options.container === 'string' 
                ? document.querySelector(options.container) 
                : options.container;

            if (!this.container) {
                console.error('[EditorialEditor] Container element not found:', options.container);
                return;
            }

            this.toolbarContainer = typeof options.toolbarContainer === 'string'
                ? document.querySelector(options.toolbarContainer)
                : options.toolbarContainer;

            this.mobileToolbarContainer = typeof options.mobileToolbarContainer === 'string'
                ? document.querySelector(options.mobileToolbarContainer)
                : options.mobileToolbarContainer;

            this.placeholder = options.placeholder || 'Tell your story...';
            this.uploadUrl = options.uploadUrl || '/upload/image';
            this.onTextChange = options.onTextChange || null;
            this.initialContent = options.initialContent || '';
            this.draftKey = options.draftKey || 'blogggle_story_draft';

            // Internal state
            this.history = [];
            this.historyIndex = -1;
            this.isComposing = false;
            this.savedSelectionRange = null;

            this.init();
        }

        init() {
            this.setupCanvas();
            this.setupToolbars();
            this.setupBubbleToolbar();
            this.setupLinkPopover();
            this.setupEventHandlers();
            this.setupMediaDragDrop();

            if (this.initialContent) {
                this.setHTML(this.initialContent);
            } else {
                this.checkDefaultContent();
            }

            this.recordSnapshot();
            this.updateWordCount();
        }

        setupCanvas() {
            this.container.setAttribute('contenteditable', 'true');
            this.container.setAttribute('role', 'textbox');
            this.container.setAttribute('aria-multiline', 'true');
            this.container.setAttribute('spellcheck', 'true');
            this.container.setAttribute('data-placeholder', this.placeholder);
            this.container.classList.add('editorial-editor-canvas');

            // Ensure baseline styles
            this.container.style.outline = 'none';
            this.container.style.minHeight = '50vh';
        }

        checkDefaultContent() {
            const html = this.container.innerHTML.trim();
            if (!html || html === '<br>' || html === '<p><br></p>') {
                this.container.innerHTML = '<p><br></p>';
            }
        }

        /* ----------------------------------------------------
         * TOOLBAR CREATION & RENDERING
         * ---------------------------------------------------- */
        setupToolbars() {
            // Build the main toolbar element
            this.toolbarEl = document.createElement('div');
            this.toolbarEl.className = 'editorial-toolbar flex items-center gap-1 py-1.5 px-2 bg-surface-container-low/90 backdrop-blur-md rounded-2xl border border-outline-variant/30 shadow-sm w-max max-w-full overflow-x-auto hide-scrollbar select-none';

            const tools = [
                { id: 'undo', icon: 'undo', title: 'Undo (⌘Z)', action: () => this.undo() },
                { id: 'redo', icon: 'redo', title: 'Redo (⌘⇧Z)', action: () => this.redo() },
                { type: 'separator' },
                { id: 'format-p', label: 'P', title: 'Paragraph', action: () => this.formatBlock('p') },
                { id: 'format-h1', label: 'H1', title: 'Heading 1 (#)', action: () => this.formatBlock('h1') },
                { id: 'format-h2', label: 'H2', title: 'Heading 2 (##)', action: () => this.formatBlock('h2') },
                { id: 'format-h3', label: 'H3', title: 'Heading 3 (###)', action: () => this.formatBlock('h3') },
                { type: 'separator' },
                { id: 'bold', icon: 'format_bold', title: 'Bold (⌘B)', action: () => this.execInline('bold') },
                { id: 'italic', icon: 'format_italic', title: 'Italic (⌘I)', action: () => this.execInline('italic') },
                { id: 'underline', icon: 'format_underlined', title: 'Underline (⌘U)', action: () => this.execInline('underline') },
                { id: 'strike', icon: 'format_strikethrough', title: 'Strikethrough', action: () => this.execInline('strikeThrough') },
                { id: 'code', icon: 'code', title: 'Inline Code', action: () => this.toggleInlineCode() },
                { type: 'separator' },
                { id: 'link', icon: 'link', title: 'Insert Link (⌘K)', action: () => this.promptLink() },
                { id: 'quote', icon: 'format_quote', title: 'Blockquote (>)', action: () => this.formatBlock('blockquote') },
                { id: 'code-block', icon: 'terminal', title: 'Code Block (```)', action: () => this.insertCodeBlock() },
                { id: 'ul', icon: 'format_list_bulleted', title: 'Bullet List (-)', action: () => this.execInline('insertUnorderedList') },
                { id: 'ol', icon: 'format_list_numbered', title: 'Numbered List (1.)', action: () => this.execInline('insertOrderedList') },
                { type: 'separator' },
                { id: 'image', icon: 'add_photo_alternate', title: 'Upload Image / Media', action: () => this.openMediaFilePicker() },
                { id: 'divider', icon: 'horizontal_rule', title: 'Divider (---)', action: () => this.insertDivider() },
                { id: 'clear', icon: 'format_clear', title: 'Clear Formatting', action: () => this.clearFormatting() }
            ];

            tools.forEach(tool => {
                if (tool.type === 'separator') {
                    const sep = document.createElement('div');
                    sep.className = 'w-[1px] h-5 bg-outline-variant/40 mx-1 shrink-0';
                    this.toolbarEl.appendChild(sep);
                    return;
                }

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'editorial-tb-btn w-8 h-8 rounded-lg flex items-center justify-center text-on-surface-variant hover:text-on-surface hover:bg-surface-container transition-all shrink-0 font-medium text-xs';
                btn.title = tool.title || '';
                btn.dataset.actionId = tool.id;

                if (tool.icon) {
                    btn.innerHTML = `<span class="material-symbols-outlined text-[19px] pointer-events-none">${tool.icon}</span>`;
                } else if (tool.label) {
                    btn.innerHTML = `<span class="font-serif font-bold pointer-events-none">${tool.label}</span>`;
                }

                btn.addEventListener('mousedown', (e) => {
                    e.preventDefault(); // Keep editor selection
                    tool.action();
                    this.updateToolbarActiveStates();
                });

                this.toolbarEl.appendChild(btn);
            });

            this.mountToolbar();
            window.addEventListener('resize', () => this.mountToolbar());
        }

        mountToolbar() {
            if (!this.toolbarEl) return;
            const isMobile = window.innerWidth < 1024;
            const targetContainer = isMobile ? this.mobileToolbarContainer : this.toolbarContainer;

            if (targetContainer && this.toolbarEl.parentElement !== targetContainer) {
                targetContainer.innerHTML = '';
                targetContainer.appendChild(this.toolbarEl);
            }
        }

        /* ----------------------------------------------------
         * FLOATING BUBBLE TOOLBAR (Selection Popover)
         * ---------------------------------------------------- */
        setupBubbleToolbar() {
            this.bubbleEl = document.createElement('div');
            this.bubbleEl.className = 'editorial-bubble-toolbar fixed z-[150] bg-surface-container-high/95 dark:bg-surface-container/95 backdrop-blur-md border border-outline-variant/40 shadow-xl rounded-full px-2 py-1 flex items-center gap-1 transition-all duration-150 opacity-0 pointer-events-none transform scale-95';

            const bubbleTools = [
                { id: 'bold', icon: 'format_bold', title: 'Bold (⌘B)', action: () => this.execInline('bold') },
                { id: 'italic', icon: 'format_italic', title: 'Italic (⌘I)', action: () => this.execInline('italic') },
                { id: 'link', icon: 'link', title: 'Link (⌘K)', action: () => this.promptLink() },
                { type: 'sep' },
                { id: 'h2', label: 'H2', title: 'Heading 2', action: () => this.formatBlock('h2') },
                { id: 'h3', label: 'H3', title: 'Heading 3', action: () => this.formatBlock('h3') },
                { id: 'quote', icon: 'format_quote', title: 'Quote', action: () => this.formatBlock('blockquote') },
            ];

            bubbleTools.forEach(tool => {
                if (tool.type === 'sep') {
                    const sep = document.createElement('div');
                    sep.className = 'w-[1px] h-4 bg-outline-variant/50 mx-0.5';
                    this.bubbleEl.appendChild(sep);
                    return;
                }

                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'w-7 h-7 rounded-full flex items-center justify-center text-on-surface-variant hover:text-on-surface hover:bg-surface-container-highest transition-colors text-xs font-semibold';
                btn.title = tool.title || '';
                btn.dataset.bubbleId = tool.id;

                if (tool.icon) {
                    btn.innerHTML = `<span class="material-symbols-outlined text-[17px] pointer-events-none">${tool.icon}</span>`;
                } else if (tool.label) {
                    btn.innerHTML = `<span class="font-serif font-bold pointer-events-none">${tool.label}</span>`;
                }

                btn.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    tool.action();
                    this.updateBubblePosition();
                });

                this.bubbleEl.appendChild(btn);
            });

            document.body.appendChild(this.bubbleEl);

            // Document selectionchange listener to position/hide bubble
            document.addEventListener('selectionchange', () => {
                this.updateBubblePosition();
                this.updateToolbarActiveStates();
            });
        }

        updateBubblePosition() {
            const sel = window.getSelection();
            if (!sel || sel.isCollapsed || !sel.rangeCount || !this.container.contains(sel.anchorNode)) {
                this.hideBubble();
                return;
            }

            const text = sel.toString().trim();
            if (text.length === 0) {
                this.hideBubble();
                return;
            }

            const range = sel.getRangeAt(0);
            const rect = range.getBoundingClientRect();
            if (rect.width === 0 || rect.height === 0) {
                this.hideBubble();
                return;
            }

            const bubbleWidth = this.bubbleEl.offsetWidth || 230;
            const bubbleHeight = this.bubbleEl.offsetHeight || 38;

            let top = rect.top - bubbleHeight - 10;
            let left = rect.left + (rect.width / 2) - (bubbleWidth / 2);

            // Bounds checking
            if (top < 10) top = rect.bottom + 10;
            if (left < 10) left = 10;
            if (left + bubbleWidth > window.innerWidth - 10) {
                left = window.innerWidth - bubbleWidth - 10;
            }

            this.bubbleEl.style.top = `${top}px`;
            this.bubbleEl.style.left = `${left}px`;
            this.bubbleEl.classList.remove('opacity-0', 'pointer-events-none', 'scale-95');
            this.bubbleEl.classList.add('opacity-100', 'pointer-events-auto', 'scale-100');
        }

        hideBubble() {
            if (!this.bubbleEl) return;
            this.bubbleEl.classList.remove('opacity-100', 'pointer-events-auto', 'scale-100');
            this.bubbleEl.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
        }

        /* ----------------------------------------------------
         * LINK POPOVER / MODAL
         * ---------------------------------------------------- */
        setupLinkPopover() {
            this.linkModalEl = document.createElement('div');
            this.linkModalEl.className = 'editorial-link-popover fixed z-[160] bg-surface-container-high dark:bg-surface-container border border-outline-variant/40 shadow-2xl rounded-2xl p-3 flex flex-col gap-2.5 transition-all duration-150 opacity-0 pointer-events-none transform scale-95 w-80';
            this.linkModalEl.innerHTML = `
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-on-surface flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-primary">link</span>
                        <span>Insert Link</span>
                    </span>
                    <button type="button" id="link-close-btn" class="text-on-surface-variant hover:text-on-surface text-sm">✕</button>
                </div>
                <input type="url" id="link-url-input" placeholder="https://example.com" class="w-full px-3 py-1.5 bg-surface-container-lowest border border-outline-variant/50 rounded-xl text-xs text-on-surface focus:outline-none focus:border-primary">
                <div class="flex items-center justify-between gap-2 pt-1">
                    <button type="button" id="link-remove-btn" class="text-[11px] text-error hover:underline">Remove Link</button>
                    <div class="flex items-center gap-1.5">
                        <button type="button" id="link-cancel-btn" class="px-2.5 py-1 rounded-lg text-[11px] text-on-surface-variant hover:bg-surface-container">Cancel</button>
                        <button type="button" id="link-apply-btn" class="px-3 py-1 rounded-lg text-[11px] font-semibold bg-primary text-on-primary hover:brightness-105">Apply</button>
                    </div>
                </div>
            `;

            document.body.appendChild(this.linkModalEl);

            const urlInput = this.linkModalEl.querySelector('#link-url-input');
            const applyBtn = this.linkModalEl.querySelector('#link-apply-btn');
            const cancelBtn = this.linkModalEl.querySelector('#link-cancel-btn');
            const closeBtn = this.linkModalEl.querySelector('#link-close-btn');
            const removeBtn = this.linkModalEl.querySelector('#link-remove-btn');

            const applyLink = () => {
                let url = urlInput.value.trim();
                if (url && !/^https?:\/\//i.test(url) && !url.startsWith('/') && !url.startsWith('#')) {
                    url = 'https://' + url;
                }
                this.restoreSelection();
                if (url) {
                    this.applyLinkUrl(url);
                }
                this.hideLinkPopover();
            };

            applyBtn.addEventListener('click', applyLink);
            urlInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyLink();
                } else if (e.key === 'Escape') {
                    this.hideLinkPopover();
                }
            });

            [cancelBtn, closeBtn].forEach(b => b.addEventListener('click', () => this.hideLinkPopover()));

            removeBtn.addEventListener('click', () => {
                this.restoreSelection();
                document.execCommand('unlink', false, null);
                this.hideLinkPopover();
                this.triggerChange();
            });
        }

        promptLink() {
            this.saveSelection();
            const sel = window.getSelection();
            let initialUrl = '';

            // Check if selection is already inside an anchor
            if (sel && sel.anchorNode) {
                const a = sel.anchorNode.parentElement?.closest('a');
                if (a) initialUrl = a.getAttribute('href') || '';
            }

            const input = this.linkModalEl.querySelector('#link-url-input');
            input.value = initialUrl;

            // Position popover
            let rect = { top: window.innerHeight / 2 - 100, left: window.innerWidth / 2 - 160 };
            if (this.savedSelectionRange) {
                const rangeRect = this.savedSelectionRange.getBoundingClientRect();
                if (rangeRect.top > 0) {
                    rect = {
                        top: Math.min(window.innerHeight - 150, rangeRect.bottom + 10),
                        left: Math.max(10, Math.min(window.innerWidth - 330, rangeRect.left))
                    };
                }
            }

            this.linkModalEl.style.top = `${rect.top}px`;
            this.linkModalEl.style.left = `${rect.left}px`;
            this.linkModalEl.classList.remove('opacity-0', 'pointer-events-none', 'scale-95');
            this.linkModalEl.classList.add('opacity-100', 'pointer-events-auto', 'scale-100');

            setTimeout(() => input.focus(), 50);
        }

        hideLinkPopover() {
            if (!this.linkModalEl) return;
            this.linkModalEl.classList.remove('opacity-100', 'pointer-events-auto', 'scale-100');
            this.linkModalEl.classList.add('opacity-0', 'pointer-events-none', 'scale-95');
        }

        applyLinkUrl(url) {
            const sel = window.getSelection();
            if (!sel) return;

            if (sel.isCollapsed) {
                // If collapsed, insert URL as link text
                document.execCommand('insertHTML', false, `<a href="${url}" target="_blank" rel="noopener noreferrer">${url}</a>`);
            } else {
                document.execCommand('createLink', false, url);
                // Ensure target="_blank"
                if (sel.anchorNode) {
                    const a = sel.anchorNode.parentElement?.closest('a');
                    if (a) {
                        a.target = '_blank';
                        a.rel = 'noopener noreferrer';
                    }
                }
            }
            this.triggerChange();
        }

        /* ----------------------------------------------------
         * SELECTION HELPERS
         * ---------------------------------------------------- */
        saveSelection() {
            const sel = window.getSelection();
            if (sel && sel.rangeCount > 0) {
                this.savedSelectionRange = sel.getRangeAt(0).cloneRange();
            }
        }

        restoreSelection() {
            if (!this.savedSelectionRange) return;
            const sel = window.getSelection();
            if (sel) {
                sel.removeAllRanges();
                sel.addRange(this.savedSelectionRange);
            }
        }

        /* ----------------------------------------------------
         * FORMATTING COMMANDS
         * ---------------------------------------------------- */
        execInline(command, val = null) {
            this.container.focus();
            document.execCommand(command, false, val);
            this.triggerChange();
        }

        formatBlock(tag) {
            this.container.focus();
            const currentBlock = this.getCurrentBlockElement();
            const currentTag = currentBlock ? currentBlock.tagName.toLowerCase() : 'p';

            if (currentTag === tag) {
                // Toggle off back to paragraph
                document.execCommand('formatBlock', false, '<p>');
            } else {
                document.execCommand('formatBlock', false, `<${tag}>`);
            }
            this.triggerChange();
            this.updateToolbarActiveStates();
        }

        toggleInlineCode() {
            this.container.focus();
            const sel = window.getSelection();
            if (!sel || sel.isCollapsed) return;

            const node = sel.anchorNode;
            const codeParent = node ? node.parentElement?.closest('code') : null;

            if (codeParent) {
                // Unwrap
                const text = codeParent.textContent;
                codeParent.replaceWith(document.createTextNode(text));
            } else {
                const range = sel.getRangeAt(0);
                const selectedText = range.toString();
                const codeEl = document.createElement('code');
                codeEl.textContent = selectedText;
                range.deleteContents();
                range.insertNode(codeEl);
            }
            this.triggerChange();
        }

        insertCodeBlock() {
            this.container.focus();
            const pre = document.createElement('pre');
            const code = document.createElement('code');
            code.textContent = '// Enter code here\n';
            pre.appendChild(code);
            pre.className = 'editorial-code-block';

            const sel = window.getSelection();
            if (sel && sel.rangeCount) {
                const range = sel.getRangeAt(0);
                range.insertNode(pre);

                // Add blank paragraph after pre
                const p = document.createElement('p');
                p.innerHTML = '<br>';
                pre.after(p);

                // Place cursor inside code
                range.selectNodeContents(code);
                range.collapse(false);
                sel.removeAllRanges();
                sel.addRange(range);
            }
            this.triggerChange();
        }

        insertDivider() {
            this.container.focus();
            const hr = document.createElement('hr');
            hr.className = 'my-8 border-t border-outline-variant/60';
            const p = document.createElement('p');
            p.innerHTML = '<br>';

            const sel = window.getSelection();
            if (sel && sel.rangeCount) {
                const range = sel.getRangeAt(0);
                range.insertNode(hr);
                hr.after(p);
                range.selectNodeContents(p);
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
            }
            this.triggerChange();
        }

        clearFormatting() {
            this.container.focus();
            document.execCommand('removeFormat', false, null);
            document.execCommand('formatBlock', false, '<p>');
            this.triggerChange();
        }

        getCurrentBlockElement() {
            const sel = window.getSelection();
            if (!sel || !sel.anchorNode) return null;

            let el = sel.anchorNode.nodeType === Node.ELEMENT_NODE 
                ? sel.anchorNode 
                : sel.anchorNode.parentElement;

            while (el && el !== this.container) {
                const tag = el.tagName.toLowerCase();
                if (['p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'ul', 'ol', 'li'].includes(tag)) {
                    return el;
                }
                el = el.parentElement;
            }
            return null;
        }

        updateToolbarActiveStates() {
            if (!this.toolbarEl) return;
            const currentBlock = this.getCurrentBlockElement();
            const currentTag = currentBlock ? currentBlock.tagName.toLowerCase() : 'p';

            // Inline states
            const isBold = document.queryCommandState('bold');
            const isItalic = document.queryCommandState('italic');
            const isUnderline = document.queryCommandState('underline');
            const isStrike = document.queryCommandState('strikeThrough');
            const isUL = document.queryCommandState('insertUnorderedList');
            const isOL = document.queryCommandState('insertOrderedList');

            const setBtnActive = (id, active) => {
                const btn = this.toolbarEl.querySelector(`[data-actionId="${id}"]`);
                if (btn) {
                    if (active) {
                        btn.classList.add('bg-primary/15', 'text-primary', 'font-bold');
                        btn.classList.remove('text-on-surface-variant');
                    } else {
                        btn.classList.remove('bg-primary/15', 'text-primary', 'font-bold');
                        btn.classList.add('text-on-surface-variant');
                    }
                }
            };

            setBtnActive('bold', isBold);
            setBtnActive('italic', isItalic);
            setBtnActive('underline', isUnderline);
            setBtnActive('strike', isStrike);
            setBtnActive('ul', isUL);
            setBtnActive('ol', isOL);

            setBtnActive('format-p', currentTag === 'p');
            setBtnActive('format-h1', currentTag === 'h1');
            setBtnActive('format-h2', currentTag === 'h2');
            setBtnActive('format-h3', currentTag === 'h3');
            setBtnActive('quote', currentTag === 'blockquote');
            setBtnActive('code-block', currentTag === 'pre');
        }

        /* ----------------------------------------------------
         * EVENT HANDLERS & SHORTCUTS
         * ---------------------------------------------------- */
        setupEventHandlers() {
            // 1. Keyboard shortcuts & Markdown triggers
            this.container.addEventListener('keydown', (e) => this.handleKeyDown(e));
            this.container.addEventListener('input', () => this.handleInput());
            this.container.addEventListener('paste', (e) => this.handlePaste(e));

            // Close popovers on outside click
            document.addEventListener('mousedown', (e) => {
                if (this.linkModalEl && !this.linkModalEl.contains(e.target) && !e.target.closest('[data-actionId="link"]') && !e.target.closest('[data-bubbleId="link"]')) {
                    this.hideLinkPopover();
                }
            });
        }

        handleKeyDown(e) {
            const isMeta = e.metaKey || e.ctrlKey;

            // Keyboard Shortcuts
            if (isMeta && !e.shiftKey && e.key.toLowerCase() === 'b') {
                e.preventDefault();
                this.execInline('bold');
                return;
            }
            if (isMeta && !e.shiftKey && e.key.toLowerCase() === 'i') {
                e.preventDefault();
                this.execInline('italic');
                return;
            }
            if (isMeta && !e.shiftKey && e.key.toLowerCase() === 'u') {
                e.preventDefault();
                this.execInline('underline');
                return;
            }
            if (isMeta && !e.shiftKey && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                this.promptLink();
                return;
            }
            if (isMeta && !e.shiftKey && e.key.toLowerCase() === 'z') {
                e.preventDefault();
                this.undo();
                return;
            }
            if ((isMeta && e.shiftKey && e.key.toLowerCase() === 'z') || (isMeta && e.key.toLowerCase() === 'y')) {
                e.preventDefault();
                this.redo();
                return;
            }

            // Tab handling inside code blocks
            if (e.key === 'Tab') {
                const block = this.getCurrentBlockElement();
                if (block && block.tagName.toLowerCase() === 'pre') {
                    e.preventDefault();
                    document.execCommand('insertText', false, '  ');
                    return;
                }
            }

            // Markdown triggers on Space or Enter
            if (e.key === ' ' || e.key === 'Spacebar') {
                if (this.checkMarkdownTriggers()) {
                    e.preventDefault();
                    return;
                }
            }
        }

        checkMarkdownTriggers() {
            const sel = window.getSelection();
            if (!sel || !sel.rangeCount || !sel.isCollapsed) return false;

            const block = this.getCurrentBlockElement();
            if (!block) return false;

            // Only trigger on normal paragraphs
            if (block.tagName.toLowerCase() !== 'p') return false;

            const text = block.textContent || '';

            if (text === '#') {
                block.innerHTML = '<br>';
                this.formatBlock('h1');
                return true;
            }
            if (text === '##') {
                block.innerHTML = '<br>';
                this.formatBlock('h2');
                return true;
            }
            if (text === '###') {
                block.innerHTML = '<br>';
                this.formatBlock('h3');
                return true;
            }
            if (text === '>') {
                block.innerHTML = '<br>';
                this.formatBlock('blockquote');
                return true;
            }
            if (text === '-' || text === '*') {
                block.innerHTML = '<br>';
                document.execCommand('insertUnorderedList', false, null);
                return true;
            }
            if (text === '1.') {
                block.innerHTML = '<br>';
                document.execCommand('insertOrderedList', false, null);
                return true;
            }
            if (text === '```') {
                block.innerHTML = '';
                this.insertCodeBlock();
                return true;
            }
            if (text === '---') {
                block.innerHTML = '';
                this.insertDivider();
                return true;
            }

            return false;
        }

        handleInput() {
            this.triggerChange();
        }

        handlePaste(e) {
            const clipboardData = e.clipboardData || window.clipboardData;
            if (!clipboardData) return;

            // 1. Check for image files in clipboard
            if (clipboardData.items && clipboardData.items.length) {
                for (let i = 0; i < clipboardData.items.length; i++) {
                    const item = clipboardData.items[i];
                    if (item.type.indexOf('image') !== -1) {
                        e.preventDefault();
                        const file = item.getAsFile();
                        if (file) this.uploadMediaFile(file);
                        return;
                    }
                }
            }

            // 2. Clean HTML pasting (remove mso styles, excessive spans)
            const html = clipboardData.getData('text/html');
            if (html) {
                e.preventDefault();
                const cleaned = this.sanitizePastedHTML(html);
                document.execCommand('insertHTML', false, cleaned);
                this.triggerChange();
                return;
            }
        }

        sanitizePastedHTML(html) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Strip style, script, head, meta
            doc.querySelectorAll('script, style, meta, link, noscript').forEach(n => n.remove());

            // Clean attributes
            const allElements = doc.body.querySelectorAll('*');
            allElements.forEach(el => {
                // Remove all attributes except href, src, alt
                const allowedAttrs = ['href', 'src', 'alt', 'target', 'rel'];
                Array.from(el.attributes).forEach(attr => {
                    if (!allowedAttrs.includes(attr.name)) {
                        el.removeAttribute(attr.name);
                    }
                });

                // Unwrap span if empty or useless
                if (el.tagName.toLowerCase() === 'span') {
                    el.replaceWith(...el.childNodes);
                }
            });

            return doc.body.innerHTML;
        }

        /* ----------------------------------------------------
         * MEDIA UPLOAD & DRAG/DROP
         * ---------------------------------------------------- */
        setupMediaDragDrop() {
            this.container.addEventListener('dragover', (e) => {
                e.preventDefault();
                this.container.classList.add('editorial-dragover');
            });

            this.container.addEventListener('dragleave', (e) => {
                e.preventDefault();
                this.container.classList.remove('editorial-dragover');
            });

            this.container.addEventListener('drop', (e) => {
                e.preventDefault();
                this.container.classList.remove('editorial-dragover');

                if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) {
                    const file = e.dataTransfer.files[0];
                    if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
                        this.uploadMediaFile(file);
                    }
                }
            });
        }

        openMediaFilePicker() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = 'image/png, image/jpeg, image/gif, image/webp, video/mp4, video/webm, video/ogg';
            input.onchange = () => {
                if (input.files && input.files[0]) {
                    this.uploadMediaFile(input.files[0]);
                }
            };
            input.click();
        }

        async uploadMediaFile(file) {
            if (!file) return;

            const MAX_FILE_SIZE = 200 * 1024 * 1024;
            if (file.size > MAX_FILE_SIZE) {
                const mb = (file.size / (1024 * 1024)).toFixed(1);
                if (typeof showToast === 'function') {
                    showToast(`File is too large (${mb}MB). Maximum allowed is 200MB.`, 'error');
                }
                return;
            }

            let fileToUpload = file;
            const isVideo = file.type.startsWith('video/') || /\.(mp4|webm|ogg|mov|mkv)$/i.test(file.name || '');

            if (isVideo && window.VideoCompressor) {
                try {
                    if (typeof showToast === 'function') showToast('Compressing video for web...', 'info');
                    fileToUpload = await window.VideoCompressor.compress(file, {
                        onProgress: (pct) => console.log(`[Video Compress] ${pct}%`),
                        onStatus: (status) => console.log('[Video Compress]', status)
                    });
                } catch (err) {
                    console.warn('Video compression skipped or failed:', err);
                }
            }

            const formData = new FormData();
            formData.append('image', fileToUpload);
            formData.append('media', fileToUpload);

            try {
                if (typeof showToast === 'function') showToast('Uploading media...', 'info');
                const res = await fetch(this.uploadUrl, {
                    method: 'POST',
                    body: formData
                });
                const data = await res.json();

                if (res.ok && data.success && data.url) {
                    this.insertMediaElement(data.url, isVideo);
                    if (typeof showToast === 'function') showToast('Media inserted into story!', 'success');
                } else {
                    if (typeof showToast === 'function') showToast(data.message || 'Media upload failed', 'error');
                }
            } catch (err) {
                console.error('Media upload error:', err);
                if (typeof showToast === 'function') showToast('Upload failed. Please try again.', 'error');
            }
        }

        insertMediaElement(url, isVideo = false) {
            this.container.focus();
            const figure = document.createElement('figure');
            figure.className = 'my-8 rounded-2xl overflow-hidden border border-outline-variant/30 bg-surface-container-high/40 group relative';

            if (isVideo) {
                figure.innerHTML = `
                    <video src="${url}" controls class="w-full max-h-[550px] rounded-xl object-contain bg-black"></video>
                `;
            } else {
                figure.innerHTML = `
                    <img src="${url}" alt="Post image" class="w-full h-auto max-h-[600px] object-cover rounded-xl transition-opacity">
                `;
            }

            const p = document.createElement('p');
            p.innerHTML = '<br>';

            const sel = window.getSelection();
            if (sel && sel.rangeCount) {
                const range = sel.getRangeAt(0);
                range.insertNode(figure);
                figure.after(p);
                range.selectNodeContents(p);
                range.collapse(true);
                sel.removeAllRanges();
                sel.addRange(range);
            } else {
                this.container.appendChild(figure);
                this.container.appendChild(p);
            }

            this.triggerChange();
        }

        /* ----------------------------------------------------
         * HISTORY / UNDO & REDO
         * ---------------------------------------------------- */
        recordSnapshot() {
            const html = this.getHTML();
            // Avoid duplicate snapshots
            if (this.historyIndex >= 0 && this.history[this.historyIndex] === html) {
                return;
            }

            // Truncate redo history
            this.history = this.history.slice(0, this.historyIndex + 1);
            this.history.push(html);

            // Limit history stack
            if (this.history.length > 50) {
                this.history.shift();
            } else {
                this.historyIndex++;
            }
        }

        undo() {
            if (this.historyIndex > 0) {
                this.historyIndex--;
                this.container.innerHTML = this.history[this.historyIndex];
                this.triggerChange(false);
            }
        }

        redo() {
            if (this.historyIndex < this.history.length - 1) {
                this.historyIndex++;
                this.container.innerHTML = this.history[this.historyIndex];
                this.triggerChange(false);
            }
        }

        /* ----------------------------------------------------
         * DRAFTING & WORD STATS
         * ---------------------------------------------------- */
        triggerChange(record = true) {
            if (record) {
                if (this.snapshotTimeout) clearTimeout(this.snapshotTimeout);
                this.snapshotTimeout = setTimeout(() => this.recordSnapshot(), 500);
            }

            const html = this.getHTML();
            const text = this.getText();
            const stats = this.calcStats(text);

            if (this.onTextChange) {
                this.onTextChange(html, text, stats);
            }

            this.updateWordCount(stats);
        }

        calcStats(text) {
            const trimmed = text.trim();
            const words = trimmed.length > 0 ? trimmed.split(/\s+/).length : 0;
            const readingTime = Math.max(1, Math.ceil(words / 200));
            return { words, readingTime, chars: trimmed.length };
        }

        updateWordCount(stats = null) {
            if (!stats) stats = this.calcStats(this.getText());
            const wcEl = document.getElementById('word-count-text');
            if (wcEl) {
                wcEl.textContent = `${stats.words} words · ${stats.readingTime} min read`;
            }
        }

        /* ----------------------------------------------------
         * PUBLIC METHODS
         * ---------------------------------------------------- */
        getHTML() {
            if (!this.container) return '';
            let html = this.container.innerHTML.trim();
            if (html === '<p><br></p>' || html === '<p></p>' || html === '<br>') {
                return '';
            }
            return html;
        }

        getText() {
            if (!this.container) return '';
            return this.container.innerText || this.container.textContent || '';
        }

        setHTML(html) {
            if (!this.container) return;
            this.container.innerHTML = html || '<p><br></p>';
            this.checkDefaultContent();
            this.recordSnapshot();
            this.updateWordCount();
        }

        focus() {
            if (this.container) this.container.focus();
        }

        clear() {
            this.setHTML('<p><br></p>');
        }
    }

    // Expose to window
    window.EditorialEditor = EditorialEditor;

})(window);
