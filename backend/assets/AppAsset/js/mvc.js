if (typeof(namespaces) == 'undefined') {
    var namespaces = {};
}

/**
 * Returns pointer to a custom namespace which you specified as string
 * @param string name namespace to get pointer or create (comma separated string)
 * @returns namespace object
 */
function namespace(name)
{
    var parts = name.split('.');
    var parent = null;
    if (typeof(namespaces[parts[0]]) == 'undefined') {
        eval('parent = typeof(' + parts[0] + ') == "undefined" ? {} : ' + parts[0]);
        namespaces[parts[0]] = parent;
    } else {
        parent = namespaces[parts[0]];
    }
    parts = parts.slice(1);
    for (var i = 0; i < parts.length; ++i) {
        if (typeof parent[parts[i]] == 'undefined') {
            parent[parts[i]] = {};
        }
        parent = parent[parts[i]];
    }
    return parent;
};

var application = namespace('application');
var pedsovet = namespace('pedsovet');
window.pedsovet = window.application = application = pedsovet;