(($) => {
    $(document).ready(() => {
        // Handle searching in settings table.
        $.fn.copyToClipboard = (text) => {
            const $temp = $('<input>')
            $('body').append($temp)
            $temp.val(text).select()
            document.execCommand('copy')
            $temp.remove()
        }
        $('.custom-php-settings-table td:nth-child(4) span').click(function () {
            const tds = $(this).parents('tr').find('td')
            const cp = tds[0].innerHTML + '=' + tds[1].innerHTML
            $().copyToClipboard(cp)
            $(this).parents('tr').effect('pulsate', { times: 1 }, 1000);
        })
        $.fn.restripe = () => {
            $('.custom-php-settings-table tr:visible').each(function (index) {
                $(this).toggleClass('striped', !!(index & 1))
            })
        }
        $('#cbkModified').on('change', function (e) {
            if (this.checked) {
                $('input[name="search"]').val('')
                $('.custom-php-settings-table tr:not(:first)').hide()
                $('.custom-php-settings-table tr.modified').show()
            } else {
                $('.custom-php-settings-table tr:not(:first)').show()
            }
            $().restripe()
        })
        $('input[name="search"]').on('keyup', function (e) {
            $('#cbkModified').prop('checked', '')
            if (e.keyCode === 13) {
                const s = this.value.toLowerCase()
                $('.custom-php-settings-table tr').show()
                if (!s.length) {
                    $().restripe()
                    return
                }
                const trs = $('.custom-php-settings-table tr:not(:first)')
                trs.map((k, v) => {
                    const td = $(v).find('td:first')
                    let found = $(td).text().toLowerCase().includes(s)
                    if (!found) {
                        $('.custom-php-settings-table td')
                        $(v).hide()
                    }
                    return found
                })
                $().restripe()
            }
        })
        // Handle dismissible notifications.
        $('.custom-php-settings-notice.notice.is-dismissible').each((a, el) => {
            $('.notice-dismiss', el).on('click', () => {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'custom_php_settings_dismiss_notice',
                        _ajax_nonce: data._nonce,
                        id: $(el).attr('id').split('-')[1],
                    },
                })
                    .done(() => {
                        if (data.debug) {
                            console.log('success');
                        }
                        el.remove();
                    })
                    .fail(() => {
                        if (data.debug) {
                            console.log('error');
                        }
                    })
                    .always(() => {
                        if (data.debug) {
                            console.log('complete');
                        }
                    });
            });
        });
    });
})(jQuery);
