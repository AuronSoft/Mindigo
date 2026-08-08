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

        // Đếm số thành viên đã chọn trong modal thêm thành viên
        const updateAddMemberCount = function () {
            const countEl = document.getElementById('discussion-add-member-count');
            const checks = document.querySelectorAll('#discussion-add-members input[name="member_ids[]"]:checked');
            if (countEl) countEl.textContent = checks.length + ' @lang('teacher-discussion::app.selected')';
        };
        document.querySelectorAll('#discussion-add-members input[name="member_ids[]"]').forEach(function (check) {
            check.addEventListener('change', updateAddMemberCount);
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

        // ==== Phase 3: Reply ====
        const replyBar = document.querySelector('[data-discussion-reply-bar]');
        const replyId = document.querySelector('[data-discussion-reply-id]');
        const replySender = document.querySelector('[data-discussion-reply-sender]');
        const replyPreview = document.querySelector('[data-discussion-reply-preview]');
        const setReply = function (sender, preview, id) {
            if (replySender) replySender.textContent = sender || '';
            if (replyPreview) replyPreview.textContent = preview || '';
            if (replyId) replyId.value = id || '';
            if (replyBar) replyBar.classList.remove('hidden');
            replyBar?.classList.add('flex');
        };
        const clearReply = function () {
            if (replyId) replyId.value = '';
            if (replyBar) {
                replyBar.classList.add('hidden');
                replyBar.classList.remove('flex');
            }
        };
        document.querySelectorAll('[data-discussion-reply]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                setReply(btn.dataset.msgSender, btn.dataset.msgPreview, btn.dataset.msgId);
                input?.focus();
            });
        });
        document.querySelector('[data-discussion-reply-cancel]')?.addEventListener('click', clearReply);

        // ==== Phase 3: Edit ====
        const updateUrl = form ? form.dataset.updateUrl : null;
        document.querySelectorAll('[data-discussion-edit]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const row = btn.closest('[data-discussion-message-row]');
                if (!row) return;
                const bodyWrap = row.querySelector('[data-message-body]');
                if (!bodyWrap) return;
                if (bodyWrap.querySelector('textarea')) return;
                const original = bodyWrap.textContent || '';
                const textarea = document.createElement('textarea');
                textarea.className = 'block w-full resize-none rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-semibold text-slate-800 outline-none focus:border-green-400';
                textarea.rows = 3;
                textarea.maxLength = 2000;
                textarea.value = original;
                const actions = document.createElement('div');
                actions.className = 'mt-2 flex items-center justify-end gap-2';
                const save = document.createElement('button');
                save.type = 'button';
                save.textContent = '@lang('teacher-discussion::app.save')';
                save.className = 'rounded-lg bg-green-600 px-3 py-1 text-xs font-black text-white hover:bg-green-500';
                const cancel = document.createElement('button');
                cancel.type = 'button';
                cancel.textContent = '@lang('teacher-discussion::app.cancel_edit')';
                cancel.className = 'rounded-lg bg-slate-100 px-3 py-1 text-xs font-black text-slate-600 hover:bg-slate-200';
                actions.appendChild(cancel);
                actions.appendChild(save);
                bodyWrap.innerHTML = '';
                bodyWrap.appendChild(textarea);
                bodyWrap.appendChild(actions);
                textarea.focus();
                const finish = function () { bodyWrap.innerHTML = original; };
                cancel.addEventListener('click', finish);
                save.addEventListener('click', function () {
                    const body = textarea.value.trim();
                    if (!body || !updateUrl) return finish();
                    const url = updateUrl.replace('__MESSAGE_ID__', btn.dataset.msgId);
                    fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '', 'X-HTTP-METHOD-OVERRIDE': 'PATCH', 'Accept': 'application/json' },
                        body: JSON.stringify({ body: body }),
                    }).then(function (res) {
                        if (!res.ok) { finish(); return; }
                        const p = document.createElement('p');
                        p.className = 'whitespace-pre-line wrap-break-word text-sm font-semibold leading-6';
                        p.textContent = body;
                        bodyWrap.innerHTML = '';
                        bodyWrap.appendChild(p);
                        row.dataset.messageText = body.toLowerCase();
                    }).catch(finish);
                });
            });
        });

        // ==== Phase 3: Delete ====
        document.querySelectorAll('[data-discussion-delete]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                if (!window.confirm('@lang('teacher-discussion::app.delete_message_confirmation')')) return;
                btn.closest('[data-discussion-delete-form]')?.submit();
            });
        });

        // ==== Phase 3: Reactions picker ====
        const reactUrl = form ? form.dataset.reactUrl : null;
        const renderReactions = function (row, reactions) {
            const container = row.querySelector('[data-reactions-display]');
            if (!container) return;
            container.innerHTML = '';
            if (reactions && reactions.length) {
                reactions.forEach(function (r) {
                    const chip = document.createElement('button');
                    chip.type = 'button';
                    chip.className = 'flex items-center gap-1 rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[11px] font-bold text-slate-700 shadow-sm transition hover:border-green-300';
                    chip.innerHTML = '<span>' + r.emoji + '</span><span>' + r.count + '</span>';
                    container.appendChild(chip);
                });
            }
        };
        const submitReaction = function (messageId, emoji) {
            if (!reactUrl) return;
            const url = reactUrl.replace('__MESSAGE_ID__', messageId);
            const body = new URLSearchParams();
            body.append('emoji', emoji);
            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                body: body,
            }).catch(function () {});
        };
        document.querySelectorAll('[data-discussion-react]').forEach(function (chip) {
            chip.addEventListener('click', function () {
                submitReaction(chip.closest('[data-discussion-message-row]')?.dataset.msgId, chip.dataset.emoji);
            });
        });
        document.querySelectorAll('[data-discussion-reactions] form').forEach(function (f) {
            f.addEventListener('submit', function (e) {
                e.preventDefault();
                const emoji = f.querySelector('input[name="emoji"]').value;
                const row = f.closest('[data-discussion-message-row]');
                submitReaction(row?.dataset.msgId, emoji);
                const menu = f.closest('[data-discussion-reactions]');
                if (menu) { menu.classList.add('hidden'); menu.classList.remove('flex'); }
            });
        });
        document.querySelectorAll('[data-discussion-react-picker]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const menu = btn.closest('[data-discussion-message-row]')?.querySelector('[data-discussion-reactions]');
                if (!menu) return;
                const isOpen = menu.classList.contains('flex');
                document.querySelectorAll('[data-discussion-reactions]').forEach(function (m) {
                    m.classList.add('hidden');
                    m.classList.remove('flex');
                });
                if (!isOpen) {
                    menu.classList.remove('hidden');
                    menu.classList.add('flex');
                }
            });
        });
        document.addEventListener('click', function (event) {
            if (!event.target.closest('[data-discussion-react-picker], [data-discussion-reactions]')) {
                document.querySelectorAll('[data-discussion-reactions]').forEach(function (m) {
                    m.classList.add('hidden');
                    m.classList.remove('flex');
                });
            }
        });

        // ==== Phase 3: Typing ====
        const typingUrl = form ? form.action.replace(/\/messages$/, '/typing') : null;
        let typingTimer = null;
        let lastTypingSent = 0;
        if (input && typingUrl && window.Echo) {
            input.addEventListener('input', function () {
                const now = Date.now();
                if (now - lastTypingSent > 1500) {
                    lastTypingSent = now;
                    fetch(typingUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    }).catch(function () {});
                }
                if (typingTimer) clearTimeout(typingTimer);
                typingTimer = setTimeout(function () { lastTypingSent = 0; }, 2000);
            });
        }
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

                const attachments = data.attachments || [];
                if (attachments.length) {
                    const wrap = document.createElement('div');
                    wrap.className = 'mt-2 grid gap-2';
                    attachments.forEach(function (attachment) {
                        const urlTemplate = pane.getAttribute('data-attachment-url') || '';
                        const url = urlTemplate.replace('__ATTACHMENT_ID__', attachment.id);
                        const link = document.createElement('a');
                        link.href = url;
                        link.target = '_blank';
                        link.className = 'flex items-center gap-3 rounded-2xl no-underline ' + (mine ? 'bg-white/15 text-white' : 'bg-white p-3 text-slate-700');
                        if (!mine) {
                            link.style.padding = '0.75rem';
                        }
                        if (attachment.is_image) {
                            link.innerHTML = '<span class="truncate text-xs font-black">' + (attachment.original_name || 'Image') + '</span>';
                        } else {
                            link.innerHTML = '<span class="grid h-9 w-9 shrink-0 place-items-center rounded-xl ' + (mine ? 'bg-white/20' : 'bg-slate-100') + '">\uD83D\uDCC4</span><span class="min-w-0 flex-1"><span class="block truncate text-xs font-black">' + (attachment.original_name || 'File') + '</span><span class="block text-[11px] font-bold opacity-70">' + (attachment.size_label || '') + '</span></span>';
                        }
                        wrap.appendChild(link);
                    });
                    bubble.appendChild(wrap);
                }

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

            const typingStatus = document.querySelector('[data-discussion-typing-status]');
            const typingFallback = typingStatus ? typingStatus.textContent : '';
            let typingTimeout = null;

            window.Echo.private('discussion.' + threadId)
                .listen('.message.sent', function (event) {
                    appendMessage(event.message);
                })
                .listen('.message.updated', function (event) {
                    const row = pane.querySelector('[data-msg-id="' + event.id + '"]');
                    if (!row) return;
                    const bodyWrap = row.querySelector('[data-message-body]');
                    if (bodyWrap) {
                        const p = document.createElement('p');
                        p.className = 'whitespace-pre-line wrap-break-word text-sm font-semibold leading-6';
                        p.textContent = event.body || '';
                        bodyWrap.innerHTML = '';
                        bodyWrap.appendChild(p);
                        row.dataset.messageText = (event.body || '').toLowerCase();
                    }
                })
                .listen('.message.deleted', function (event) {
                    const row = pane.querySelector('[data-msg-id="' + event.message_id + '"]');
                    if (row) row.remove();
                })
                .listen('.message.reacted', function (event) {
                    const row = pane.querySelector('[data-msg-id="' + event.message_id + '"]');
                    if (!row) return;
                    const container = row.querySelector('[data-reactions-display]');
                    if (!container) return;
                    container.classList.remove('hidden');
                    container.classList.add('flex');
                    container.innerHTML = '';
                    if (event.reactions && event.reactions.length) {
                        event.reactions.forEach(function (r) {
                            const chip = document.createElement('button');
                            chip.type = 'button';
                            chip.className = 'flex items-center gap-1 rounded-full border border-slate-200 bg-white px-2 py-0.5 text-[11px] font-bold text-slate-700 shadow-sm transition hover:border-green-300';
                            chip.innerHTML = '<span>' + r.emoji + '</span><span>' + r.count + '</span>';
                            container.appendChild(chip);
                        });
                    }
                })
                .listen('.message.typing', function (event) {
                    if (!typingStatus) return;
                    if (String(event.user_id) === String(currentUserId)) return;
                    typingStatus.textContent = event.name + ' @lang('teacher-discussion::app.is_typing')';
                    typingStatus.classList.add('text-green-600');
                    if (typingTimeout) clearTimeout(typingTimeout);
                    typingTimeout = setTimeout(function () {
                        typingStatus.textContent = typingFallback;
                        typingStatus.classList.remove('text-green-600');
                    }, 2500);
                });

            // ==== Phase 3: Realtime room list update (item 6) ====
            const roomList = document.querySelector('[data-discussion-room-list]');
            const otherThreadIds = roomList ? roomList.getAttribute('data-discussion-thread-ids') : '';
            const roomsByThread = {};
            document.querySelectorAll('[data-discussion-room]').forEach(function (room) {
                roomsByThread[room.dataset.roomThreadId] = room;
            });
            const updateRoomOnMessage = function (threadId, data) {
                const room = roomsByThread[threadId];
                if (!room || !roomList) return;
                const senderIsMe = String(data.sender_id) === String(currentUserId);
                const previewEl = room.querySelector('.mt-1 p');
                if (previewEl) previewEl.textContent = data.body || '@lang('teacher-discussion::app.no_messages_short')';
                if (!senderIsMe) {
                    const badge = room.querySelector('.bg-green-600');
                    const count = badge ? parseInt(badge.textContent, 10) || 0 : 0;
                    const newCount = count + 1;
                    if (badge) {
                        badge.textContent = newCount > 99 ? '99+' : newCount;
                    } else {
                        const newBadge = document.createElement('span');
                        newBadge.className = 'grid h-5 min-w-5 place-items-center rounded-full bg-green-600 px-1.5 text-[10px] font-black text-white';
                        newBadge.textContent = '1';
                        const row = room.querySelector('.mt-1');
                        if (row) row.appendChild(newBadge);
                    }
                    room.dataset.unread = 'true';
                }
                roomList.insertBefore(room, roomList.firstChild);
            };
            if (otherThreadIds && window.Echo) {
                otherThreadIds.split(',').forEach(function (id) {
                    if (!id || String(id) === String(threadId)) return;
                    window.Echo.private('discussion.' + id)
                        .listen('.message.sent', function (event) {
                            const m = event.message || {};
                            updateRoomOnMessage(id, m);
                        });
                });
            }
        })();
    </script>
@endif
