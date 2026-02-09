/**
 * Hamnaghsheh Ticketing System - Frontend JavaScript
 *
 * @package Hamnaghsheh_Ticketing
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // File upload validation
        $('input[type="file"]').on('change', function() {
            const files = this.files;
            const maxSize = 5 * 1024 * 1024; // 5MB
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 
                                  'text/plain', 'application/msword', 
                                  'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                // Check file size
                if (file.size > maxSize) {
                    alert('فایل ' + file.name + ' بیش از 5 مگابایت است.');
                    this.value = '';
                    return false;
                }
                
                // Check file type
                if (!allowedTypes.includes(file.type)) {
                    alert('نوع فایل ' + file.name + ' مجاز نیست.');
                    this.value = '';
                    return false;
                }
            }
        });

        // Form validation
        $('.ticket-form').on('submit', function(e) {
            const title = $(this).find('input[name="title"]').val();
            const category = $(this).find('select[name="category"]').val();
            const message = $(this).find('textarea[name="message"]').val();
            
            if (!title || !category || !message) {
                e.preventDefault();
                alert('لطفا تمام فیلدهای الزامی را پر کنید.');
                return false;
            }
            
            if (title.length > 255) {
                e.preventDefault();
                alert('عنوان نباید بیشتر از 255 کاراکتر باشد.');
                return false;
            }
        });

        // Auto-resize textarea (only within ticketing wrapper)
        $('.hamnaghsheh-tickets-wrapper textarea, .hamnaghsheh-ticket-detail-wrapper textarea').on('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Smooth scroll to form
        if (window.location.hash === '#create') {
            const form = document.getElementById('create-ticket-form');
            if (form) {
                form.style.display = 'block';
                form.scrollIntoView({ behavior: 'smooth' });
            }
        }

        // Status filter change
        $('.status-filter').on('change', function() {
            const status = $(this).val();
            const url = new URL(window.location);
            
            if (status === 'all') {
                url.searchParams.delete('status');
            } else {
                url.searchParams.set('status', status);
            }
            
            window.location.href = url.toString();
        });

        // Attachment preview for images
        $('input[type="file"]').on('change', function(e) {
            const files = e.target.files;
            const previewContainer = $(this).siblings('.attachment-preview');
            
            if (previewContainer.length === 0) {
                $(this).after('<div class="attachment-preview" style="margin-top: 10px;"></div>');
            }
            
            $('.attachment-preview').empty();
            
            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        const img = $('<img>').attr('src', e.target.result)
                                              .css({
                                                  'max-width': '100px',
                                                  'max-height': '100px',
                                                  'margin': '5px',
                                                  'border': '1px solid #ddd',
                                                  'border-radius': '4px'
                                              });
                        $('.attachment-preview').append(img);
                    };
                    
                    reader.readAsDataURL(file);
                } else {
                    const fileInfo = $('<div>').text('📎 ' + file.name)
                                               .css({
                                                   'padding': '5px',
                                                   'margin': '5px 0',
                                                   'background': '#f5f5f5',
                                                   'border-radius': '4px'
                                               });
                    $('.attachment-preview').append(fileInfo);
                }
            }
        });

        // Confirm before leaving page with unsaved changes
        let formChanged = false;
        
        $('form input, form textarea, form select').on('change', function() {
            formChanged = true;
        });
        
        $('form').on('submit', function() {
            formChanged = false;
        });
        
        $(window).on('beforeunload', function(e) {
            if (formChanged) {
                const message = 'تغییرات شما ذخیره نشده است. آیا مطمئن هستید که می‌خواهید این صفحه را ترک کنید؟';
                e.returnValue = message;
                return message;
            }
        });

    });

})(jQuery);
