_('prototype').in_array = function (needle, haystack, strict) {
    var key = ''
    var strict = !!strict
    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true
                }
            }
          } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true
            }
        }
    }
    return false
}

priya.in_array = _('prototype').in_array;