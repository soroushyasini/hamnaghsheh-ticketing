/**
 * Hamnaghsheh Ticketing System - Admin JavaScript
 *
 * @package Hamnaghsheh_Ticketing
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Quick status change from list
        $('.quick-status-change').on('change', function() {
            const ticketId = $(this).data('ticket-id');
            const status = $(this).val();
            
            $.ajax({
                url: hamnaghshehAdminTickets.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hamnaghsheh_admin_update_status',
                    nonce: hamnaghshehAdminTickets.nonce,
                    ticket_id: ticketId,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        });

        // Quick priority change from list
        $('.quick-priority-change').on('change', function() {
            const ticketId = $(this).data('ticket-id');
            const priority = $(this).val();
            
            $.ajax({
                url: hamnaghshehAdminTickets.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hamnaghsheh_admin_set_priority',
                    nonce: hamnaghshehAdminTickets.nonce,
                    ticket_id: ticketId,
                    priority: priority
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        });

        // Bulk actions
        $('#doaction, #doaction2').on('click', function(e) {
            const action = $(this).siblings('select[name="action"]').val();
            
            if (action === '-1') {
                e.preventDefault();
                return false;
            }
            
            const selectedTickets = $('input[name="ticket[]"]:checked');
            
            if (selectedTickets.length === 0) {
                e.preventDefault();
                alert('لطفا حداقل یک تیکت را انتخاب کنید.');
                return false;
            }
            
            // Confirm action
            const confirmMessage = 'آیا از انجام این عملیات بر روی ' + selectedTickets.length + ' تیکت اطمینان دارید؟';
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return false;
            }
        });

        // Select all checkbox
        $('#cb-select-all-1, #cb-select-all-2').on('change', function() {
            const checked = $(this).prop('checked');
            $('input[name="ticket[]"]').prop('checked', checked);
        });

        // Auto-save draft note
        let noteAutoSaveTimer;
        $('textarea[name="note"]').on('input', function() {
            clearTimeout(noteAutoSaveTimer);
            const note = $(this).val();
            
            if (note.length > 10) {
                noteAutoSaveTimer = setTimeout(function() {
                    localStorage.setItem('ticket_note_draft', note);
                }, 1000);
            }
        });

        // Restore draft note
        const draftNote = localStorage.getItem('ticket_note_draft');
        if (draftNote) {
            $('textarea[name="note"]').val(draftNote);
        }

        // Clear draft after submission
        $('#admin-note-form').on('submit', function() {
            localStorage.removeItem('ticket_note_draft');
        });

        // File upload validation
        $('input[type="file"]').on('change', function() {
            const files = this.files;
            const maxSize = 5 * 1024 * 1024; // 5MB
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                if (file.size > maxSize) {
                    alert('فایل ' + file.name + ' بیش از 5 مگابایت است.');
                    this.value = '';
                    return false;
                }
            }
        });

        // Keyboard shortcuts
        $(document).on('keydown', function(e) {
            // Ctrl/Cmd + S to save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                
                if ($('#admin-reply-form').length) {
                    $('#admin-reply-form').submit();
                } else if ($('#admin-note-form').length) {
                    $('#admin-note-form').submit();
                }
            }
        });

        // Ticket filters - clear button
        $('.clear-filters').on('click', function(e) {
            e.preventDefault();
            window.location.href = '?page=hamnaghsheh-tickets';
        });

        // Real-time search
        let searchTimer;
        $('input[name="search"]').on('input', function() {
            clearTimeout(searchTimer);
            const searchTerm = $(this).val();
            
            if (searchTerm.length >= 3 || searchTerm.length === 0) {
                searchTimer = setTimeout(function() {
                    $('form').submit();
                }, 500);
            }
        });

        // Ticket stats refresh
        function refreshTicketStats() {
            $.ajax({
                url: hamnaghshehAdminTickets.ajaxurl,
                type: 'POST',
                data: {
                    action: 'hamnaghsheh_get_ticket_stats',
                    nonce: hamnaghshehAdminTickets.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update stats if changed
                        // This would update the badge count in menu
                    }
                }
            });
        }

        // Refresh stats every 60 seconds
        setInterval(refreshTicketStats, 60000);

        // Confirmation dialogs
        $('.delete-ticket').on('click', function(e) {
            if (!confirm('آیا از حذف این تیکت اطمینان دارید؟ این عمل قابل بازگشت نیست.')) {
                e.preventDefault();
                return false;
            }
        });

        // Copy ticket number to clipboard
        $('.copy-ticket-number').on('click', function(e) {
            e.preventDefault();
            const ticketNumber = $(this).data('ticket-number');
            
            navigator.clipboard.writeText(ticketNumber).then(function() {
                alert('شماره تیکت کپی شد: ' + ticketNumber);
            });
        });

        // Expand/collapse note sections
        $('.note-toggle').on('click', function() {
            const noteId = $(this).data('note-id');
            $('#note-' + noteId).slideToggle();
        });

        // Highlight mentions in messages
        $('.reply-message, .note-content').each(function() {
            const content = $(this).html();
            const highlightedContent = content.replace(/@(\w+)/g, '<span class="mention">@$1</span>');
            $(this).html(highlightedContent);
        });

    });

})(jQuery);
