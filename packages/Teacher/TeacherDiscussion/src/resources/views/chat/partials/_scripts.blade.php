<script>
    document.addEventListener('DOMContentLoaded', function () {
        const pane = document.querySelector('[data-discussion-messages]');
        if (pane) {
            pane.scrollTop = pane.scrollHeight;
        }

        const input = document.querySelector('[data-discussion-input]');
        if (input) {
            input.addEventListener('input', function () {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 128) + 'px';
            });
        }

        const search = document.querySelector('[data-discussion-search]');
        const rooms = document.querySelectorAll('[data-discussion-room]');
        const tabs = document.querySelectorAll('[data-discussion-tab]');
        const tabPanes = document.querySelectorAll('[data-discussion-tab-pane]');
        if (tabs.length) {
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    const target = tab.dataset.discussionTab;
                    tabs.forEach(function (t) {
                        t.classList.toggle('bg-green-50', t.dataset.discussionTab === target);
                        t.classList.toggle('text-green-700', t.dataset.discussionTab === target);
                        t.classList.toggle('border-green-100', t.dataset.discussionTab === target);
                    });
                    tabPanes.forEach(function (pane) {
                        pane.classList.toggle('hidden', pane.dataset.discussionTabPane !== target);
                    });
                });
            });
        }

        const fileInput = document.querySelector('[data-discussion-files]');
        const fileTrigger = document.querySelector('[data-discussion-file-trigger]');
        const imageTrigger = document.querySelector('[data-discussion-image-trigger]');
        const filePreview = document.querySelector('[data-discussion-file-preview]');
        const openFiles = function (accept) {
            if (!fileInput) return;
            fileInput.setAttribute('accept', accept);
            fileInput.click();
        };
        if (fileInput) {
            if (fileTrigger) {
                fileTrigger.addEventListener('click', function () {
                    openFiles('image/*,.pdf,.txt,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar');
                });
            }
            if (imageTrigger) {
                imageTrigger.addEventListener('click', function () {
                    openFiles('image/*');
                });
            }
            fileInput.addEventListener('change', function () {
                if (!filePreview) return;
                const files = Array.from(fileInput.files || []);
                filePreview.innerHTML = '';
                filePreview.classList.toggle('hidden', files.length === 0);
                files.slice(0, 6).forEach(function (file) {
                    const item = document.createElement('span');
                    item.className = 'inline-flex max-w-52 items-center gap-1 rounded-full bg-green-50 px-3 py-1 text-[11px] font-black text-green-700';
                    item.textContent = file.name;
                    filePreview.appendChild(item);
                });
            });
        }

        const infoPanel = document.querySelector('[data-discussion-info-panel]');
        if (infoPanel) {
            document.querySelectorAll('[data-discussion-info-toggle]').forEach(function (infoToggle) {
                infoToggle.addEventListener('click', function () {
                    infoPanel.classList.toggle('hidden');
                });
            });
        }

        let activeRoomFilter = 'all';
        const applyRoomFilters = function () {
            const keyword = search ? search.value.trim().toLowerCase() : '';
            let visible = 0;
            rooms.forEach(function (room) {
                const matchesSearch = room.dataset.search.includes(keyword);
                const matchesFilter = activeRoomFilter === 'all'
                    || (activeRoomFilter === 'unread' && room.dataset.unread === 'true')
                    || (activeRoomFilter === 'groups' && ['group', 'class'].includes(room.dataset.roomType))
                    || (activeRoomFilter === 'direct' && room.dataset.roomType === 'direct')
                    || (activeRoomFilter === 'pinned' && room.dataset.roomPinned === 'true')
                    || (activeRoomFilter === 'muted' && room.dataset.roomMuted === 'true');
                const show = matchesSearch && matchesFilter;
                room.classList.toggle('hidden', !show);
                if (show) visible++;
            });
            const empty = document.querySelector('[data-discussion-filter-empty]');
            if (empty) empty.classList.toggle('hidden', visible > 0);
        };
        if (search) search.addEventListener('input', applyRoomFilters);
        document.querySelectorAll('[data-discussion-list-filter]').forEach(function (button) {
            button.addEventListener('click', function () {
                activeRoomFilter = button.dataset.discussionListFilter;
                document.querySelectorAll('[data-discussion-list-filter]').forEach(function (item) {
                    const active = item === button;
                    item.classList.toggle('border-green-600', active);
                    item.classList.toggle('text-green-700', active);
                    item.classList.toggle('border-transparent', !active);
                    item.classList.toggle('text-slate-500', !active);
                });
                applyRoomFilters();
            });
        });

        const messageSearch = document.querySelector('[data-discussion-message-search]');
        const messageSearchInput = document.querySelector('[data-discussion-message-search-input]');
        document.querySelectorAll('[data-discussion-message-search-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (!messageSearch) return;
                messageSearch.classList.toggle('hidden');
                if (!messageSearch.classList.contains('hidden')) messageSearchInput?.focus();
            });
        });
        document.querySelectorAll('[data-discussion-message-search-close]').forEach(function (button) {
            button.addEventListener('click', function () {
                messageSearch?.classList.add('hidden');
                if (messageSearchInput) messageSearchInput.value = '';
                document.querySelectorAll('[data-discussion-message-row]').forEach(function (row) { row.classList.remove('hidden'); });
            });
        });
        messageSearchInput?.addEventListener('input', function () {
            const keyword = messageSearchInput.value.trim().toLowerCase();
            document.querySelectorAll('[data-discussion-message-row]').forEach(function (row) {
                row.classList.toggle('hidden', keyword !== '' && !row.dataset.messageText.includes(keyword));
            });
        });

        const openModal = function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.remove('hidden');
            el.classList.add('flex');
        };
        const closeModal = function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.add('hidden');
            el.classList.remove('flex');
        };

        const classificationMenu = document.querySelector('[data-discussion-classification-menu]');
        const moreMenu = document.querySelector('[data-discussion-more-menu]');
        document.querySelector('[data-discussion-classification-toggle]')?.addEventListener('click', function (event) {
            event.stopPropagation();
            classificationMenu?.classList.toggle('hidden');
            moreMenu?.classList.add('hidden');
            this.setAttribute('aria-expanded', classificationMenu && !classificationMenu.classList.contains('hidden') ? 'true' : 'false');
        });
        document.querySelector('[data-discussion-more-toggle]')?.addEventListener('click', function (event) {
            event.stopPropagation();
            moreMenu?.classList.toggle('hidden');
            classificationMenu?.classList.add('hidden');
        });
        document.querySelectorAll('[data-discussion-classification]').forEach(function (button) {
            button.addEventListener('click', function () {
                activeRoomFilter = button.dataset.discussionClassification;
                document.querySelectorAll('[data-discussion-classification-check]').forEach(function (check) {
                    check.innerHTML = '';
                    check.classList.remove('border-green-600', 'bg-green-600');
                });
                const check = button.querySelector('[data-discussion-classification-check]');
                if (check) {
                    check.classList.add('border-green-600', 'bg-green-600');
                    check.innerHTML = '<span class="h-1.5 w-1.5 rounded-full bg-white"></span>';
                }
                classificationMenu?.classList.add('hidden');
                applyRoomFilters();
            });
        });
        document.querySelector('[data-discussion-mark-all-read-open]')?.addEventListener('click', function () {
            moreMenu?.classList.add('hidden');
            openModal('discussion-mark-all-read-modal');
        });
        document.addEventListener('click', function (event) {
            if (!event.target.closest('[data-discussion-classification-menu], [data-discussion-classification-toggle]')) {
                classificationMenu?.classList.add('hidden');
            }
            if (!event.target.closest('[data-discussion-more-menu], [data-discussion-more-toggle]')) {
                moreMenu?.classList.add('hidden');
            }
        });

        document.querySelectorAll('[data-discussion-new-group]').forEach(function (btn) {
            btn.addEventListener('click', function () { openModal('discussion-group-modal'); });
        });
        document.querySelectorAll('[data-discussion-new-direct]').forEach(function (btn) {
            btn.addEventListener('click', function () { openModal('discussion-direct-modal'); });
        });
        document.querySelectorAll('[data-discussion-modal-close]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                closeModal(btn.dataset.discussionModalClose);
            });
        });
        document.querySelectorAll('[data-discussion-modal]').forEach(function (modal) {
            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        });
        document.querySelectorAll('[data-discussion-view]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                openModal('discussion-view-' + btn.dataset.discussionView + '-modal');
            });
        });

        // Lọc danh sách trong modal (thành viên / liên hệ)
        document.querySelectorAll('[data-discussion-filter-input]').forEach(function (input) {
            input.addEventListener('input', function () {
                const keyword = this.value.trim().toLowerCase();
                const list = document.querySelector(input.dataset.discussionFilterInput);
                if (!list) return;
                list.querySelectorAll('[data-discussion-option]').forEach(function (opt) {
                    opt.classList.toggle('hidden', !opt.dataset.search.includes(keyword));
                });
            });
        });

        // Cập nhật avatar xem trước theo màu trong modal tạo nhóm
        document.querySelectorAll('input[name="theme_color"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                const preview = document.getElementById('discussion-group-avatar-preview');
                if (!preview) return;
                const color = radio.value;
                preview.className = 'mx-auto grid h-20 w-20 place-items-center rounded-full bg-' + color + '-100 text-2xl font-black text-' + color + '-700';
            });
        });

        // Đếm số thành viên đã chọn trong modal tạo nhóm
        const updateGroupCount = function () {
            const countEl = document.getElementById('discussion-group-count');
            const checks = document.querySelectorAll('input[name="member_ids[]"]:checked');
            if (countEl) countEl.textContent = checks.length + ' @lang('teacher-discussion::app.selected')';
        };
        document.querySelectorAll('input[name="member_ids[]"]').forEach(function (check) {
            check.addEventListener('change', updateGroupCount);
        });

        // Khi đóng modal, reset các ô tích chọn
        document.querySelectorAll('[data-discussion-modal-close]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                closeModal(btn.dataset.discussionModalClose);
                if (btn.dataset.discussionModalClose === 'discussion-group-modal') {
                    const groupModal = document.getElementById('discussion-group-modal');
                    if (groupModal) groupModal.querySelectorAll('input[type="checkbox"]').forEach(function (c) { c.checked = false; });
                    updateGroupCount();
                }
            });
        });
    });
</script>

@if($selectedThread)
    <script>
        (function () {
            if (!window.Echo) return;
            const pane = document.querySelector('[data-discussion-messages]');
            const form = document.querySelector('[data-discussion-form]');
            const threadId = pane ? pane.dataset.threadId : null;
            const currentUserId = form ? form.dataset.currentUser : null;
            if (!threadId) return;

            const buildBubble = function (data) {
                const mine = String(data.sender_id) === String(currentUserId);
                const senderName = (data.sender && data.sender.name) || '';
                const time = data.created_at ? new Date(data.created_at) : new Date();
                const timeLabel = time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                const initial = senderName.charAt(0).toUpperCase();

                const row = document.createElement('div');
                row.className = 'flex items-end gap-2.5 ' + (mine ? 'justify-end' : 'justify-start');

                if (!mine) {
                    const av = document.createElement('div');
                    av.className = 'grid h-9 w-9 shrink-0 place-items-center rounded-full bg-slate-100 text-xs font-black text-slate-600';
                    av.textContent = initial;
                    row.appendChild(av);
                }

                const inner = document.createElement('div');
                inner.className = 'max-w-[min(35rem,78%)]';

                const meta = document.createElement('div');
                meta.className = 'mb-1 flex items-center gap-2 px-1 ' + (mine ? 'justify-end' : '');
                if (!mine) {
                    const nm = document.createElement('span');
                    nm.className = 'text-xs font-black text-slate-700';
                    nm.textContent = senderName;
                    meta.appendChild(nm);
                }
                const tm = document.createElement('span');
                tm.className = 'text-[11px] font-bold text-slate-400';
                tm.textContent = timeLabel;
                meta.appendChild(tm);

                const bubble = document.createElement('div');
                bubble.className = 'rounded-2xl px-4 py-3 ' + (mine ? 'rounded-br-md bg-green-600 text-white' : 'rounded-bl-md bg-slate-100 text-slate-800');
                const p = document.createElement('p');
                p.className = 'whitespace-pre-line wrap-break-word text-sm font-semibold leading-6';
                p.textContent = data.body || '';
                bubble.appendChild(p);

                inner.appendChild(meta);
                inner.appendChild(bubble);
                row.appendChild(inner);
                return row;
            };

            const appendMessage = function (data) {
                if (!pane) return;
                const list = pane.querySelector('.space-y-4');
                if (!list) return;
                if (list.querySelector('[data-msg-id="' + data.id + '"]')) return;
                const row = buildBubble(data);
                row.setAttribute('data-msg-id', data.id);
                list.appendChild(row);
                pane.scrollTop = pane.scrollHeight;
            };

            window.Echo.private('discussion.' + threadId)
                .listen('.message.sent', function (event) {
                    appendMessage(event.message);
                });
        })();
    </script>
@endif
