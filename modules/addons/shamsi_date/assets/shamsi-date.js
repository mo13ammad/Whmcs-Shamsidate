(function () {
    'use strict';

    var config = window.ShamsiDateConfig || {};
    var persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    var arabicDigits = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    var monthNames = {
        jan: 1, january: 1,
        feb: 2, february: 2,
        mar: 3, march: 3,
        apr: 4, april: 4,
        may: 5,
        jun: 6, june: 6,
        jul: 7, july: 7,
        aug: 8, august: 8,
        sep: 9, sept: 9, september: 9,
        oct: 10, october: 10,
        nov: 11, november: 11,
        dec: 12, december: 12
    };
    var jalaliMonths = ['', 'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
    var jalaliWeekdays = ['یکشنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنجشنبه', 'جمعه', 'شنبه'];

    function gregorianToJalali(gy, gm, gd) {
        var gDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        var jDays = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];
        var i;

        gy -= 1600;
        gm -= 1;
        gd -= 1;

        var gDayNo = 365 * gy + Math.floor((gy + 3) / 4) - Math.floor((gy + 99) / 100) + Math.floor((gy + 399) / 400);

        for (i = 0; i < gm; i += 1) {
            gDayNo += gDays[i];
        }

        if (gm > 1 && ((gy % 4 === 0 && gy % 100 !== 0) || (gy % 400 === 0))) {
            gDayNo += 1;
        }

        gDayNo += gd;

        var jDayNo = gDayNo - 79;
        var jNp = Math.floor(jDayNo / 12053);
        jDayNo %= 12053;

        var jy = 979 + 33 * jNp + 4 * Math.floor(jDayNo / 1461);
        jDayNo %= 1461;

        if (jDayNo >= 366) {
            jy += Math.floor((jDayNo - 1) / 365);
            jDayNo = (jDayNo - 1) % 365;
        }

        for (i = 0; i < 11 && jDayNo >= jDays[i]; i += 1) {
            jDayNo -= jDays[i];
        }

        return [jy, i + 1, jDayNo + 1];
    }

    function pad(value) {
        return String(value).padStart(2, '0');
    }

    function convertDigits(value) {
        var mode = config.digitMode || 'persian';

        if (mode === 'english') {
            return value;
        }

        return String(value).replace(/[0-9]/g, function (digit) {
            return mode === 'arabic' ? arabicDigits[Number(digit)] : persianDigits[Number(digit)];
        });
    }

    function formatDate(gy, gm, gd, time) {
        var parts = gregorianToJalali(Number(gy), Number(gm), Number(gd));
        var date = new Date(Number(gy), Number(gm) - 1, Number(gd));
        var map = {
            Y: String(parts[0]),
            y: String(parts[0]).slice(-2),
            m: pad(parts[1]),
            n: String(parts[1]),
            d: pad(parts[2]),
            j: String(parts[2]),
            F: jalaliMonths[parts[1]],
            M: jalaliMonths[parts[1]].slice(0, 3),
            l: jalaliWeekdays[date.getDay()],
            D: jalaliWeekdays[date.getDay()].slice(0, 3)
        };
        var output = String(config.dateFormat || 'Y/m/d').replace(/\\?.|./g, function (token) {
            if (token.length > 1 && token.charAt(0) === '\\') {
                return token.slice(1);
            }

            return Object.prototype.hasOwnProperty.call(map, token) ? map[token] : token;
        });

        if (time && config.includeTime !== false) {
            output += ' ' + time;
        }

        return convertDigits(output);
    }

    function validDate(gy, gm, gd) {
        var date = new Date(Number(gy), Number(gm) - 1, Number(gd));

        return date.getFullYear() === Number(gy) && date.getMonth() === Number(gm) - 1 && date.getDate() === Number(gd);
    }

    function replaceDates(value) {
        var original = value;

        value = value.replace(/\b(20\d{2}|19\d{2})[-/.](0?[1-9]|1[0-2])[-/.](0?[1-9]|[12]\d|3[01])(?:[ T](\d{1,2}:\d{2}(?::\d{2})?))?\b/g, function (match, gy, gm, gd, time) {
            return validDate(gy, gm, gd) ? formatDate(gy, gm, gd, time || '') : match;
        });

        value = value.replace(/\b(0?[1-9]|[12]\d|3[01])\s+([A-Za-z]{3,9})\s+(20\d{2}|19\d{2})(?:\s+(\d{1,2}:\d{2}(?::\d{2})?))?\b/g, function (match, gd, month, gy, time) {
            var gm = monthNames[month.toLowerCase()];

            return gm && validDate(gy, gm, gd) ? formatDate(gy, gm, gd, time || '') : match;
        });

        value = value.replace(/\b([A-Za-z]{3,9})\s+(0?[1-9]|[12]\d|3[01]),?\s+(20\d{2}|19\d{2})(?:\s+(\d{1,2}:\d{2}(?::\d{2})?))?\b/g, function (match, month, gd, gy, time) {
            var gm = monthNames[month.toLowerCase()];

            return gm && validDate(gy, gm, gd) ? formatDate(gy, gm, gd, time || '') : match;
        });

        return value === original ? original : value;
    }

    function shouldSkip(node) {
        var element = node.nodeType === 1 ? node : node.parentElement;

        while (element) {
            if (element.classList && element.classList.contains('no-shamsi-date')) {
                return true;
            }

            if (/^(SCRIPT|STYLE|NOSCRIPT|CODE|PRE|TEXTAREA)$/i.test(element.tagName)) {
                return true;
            }

            element = element.parentElement;
        }

        return false;
    }

    function processTextNode(node) {
        if (!node.nodeValue || shouldSkip(node)) {
            return;
        }

        var converted = replaceDates(node.nodeValue);

        if (converted !== node.nodeValue) {
            node.nodeValue = converted;
            if (node.parentElement) {
                node.parentElement.classList.add('shamsi-date-converted');
            }
        }
    }

    function processInputs(root) {
        if (!config.convertInputs || !root.querySelectorAll) {
            return;
        }

        root.querySelectorAll('input[type="text"], input:not([type]), input[type="search"]').forEach(function (input) {
            if (shouldSkip(input) || input.dataset.shamsiDateProcessed === '1') {
                return;
            }

            var converted = replaceDates(input.value);

            if (converted !== input.value) {
                input.value = converted;
                input.dataset.shamsiDateProcessed = '1';
            }
        });
    }

    function process(root) {
        if (!config.convertTextNodes && !config.convertInputs) {
            return;
        }

        if (config.convertTextNodes !== false) {
            var walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
            var node;

            while ((node = walker.nextNode())) {
                processTextNode(node);
            }
        }

        processInputs(root);
    }

    function init() {
        process(document.body);

        if (config.observeAjax === false || !window.MutationObserver) {
            return;
        }

        var scheduled = false;
        var observer = new MutationObserver(function () {
            if (scheduled) {
                return;
            }

            scheduled = true;
            window.setTimeout(function () {
                scheduled = false;
                process(document.body);
            }, 120);
        });

        observer.observe(document.body, {childList: true, subtree: true});
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
}());
