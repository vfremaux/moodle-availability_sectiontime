YUI.add('moodle-availability_sectiontime-form', function (Y, NAME) {

/**
 * JavaScript for form editing sections conditions.
 *
 * @module moodle-availability_sectiontime-form
 */
M.availability_sectiontime = M.availability_sectiontime || {};

/**
 * @class M.availability_sectiontime.form
 * @extends M.core_availability.plugin
 */
M.availability_sectiontime.form = Y.Object(M.core_availability.plugin);

/**
 * Groupings available for selection (alphabetical order).
 *
 * @property sections
 * @type Array
 */
M.availability_sectiontime.form.sections = null;

/**
 * Initialises this plugin.
 *
 * @method initInner
 * @param {Array} standardFields Array of objects with .field, .display
 * @param {Array} customFields Array of objects with .field, .display
 */
M.availability_sectiontime.form.initInner = function(standardFields) {
    this.sections = standardFields;
};

M.availability_sectiontime.form.getNode = function(json) {
    // Create HTML structure.
    var strings = M.str.availability_sectiontime;
    var html = '<span class="availability-group">';
    html += '<label><span class="accesshide"></span> ' + strings.conditiontitle +
            ' <input name="timespent" type="text" style="width: 10em" title="' +
            strings.conditiontitle + '"/></label>';
    html += '<label> ' + strings.insection + ' ' +
            '<select name="sectionid">' +
            '<option value="choose">' + M.str.moodle.choosedots + '</option>';
    var fieldInfo;
    for (var i = 0; i < this.sections.length; i++) {
        fieldInfo = this.sections[i];
        // String has already been escaped using format_string.
        html += '<option value="s_' + fieldInfo.field + '">' + fieldInfo.display + '</option>';
    }
    html += '</select></label>';
    html += '</span>';
    var node = Y.Node.create('<span>' + html + '</span>');

    // Set initial values if specified.
    if (json.s !== undefined &&
            node.one('select[name=sectionid] > option[value=s_' + json.s + ']')) {
        node.one('select[name=sectionid]').set('value', 's_' + json.s);
    }
    if (json.t !== undefined) {
        node.one('input[name=timespent]').set('value', json.t);
    }

    // Add event handlers (first time only).
    if (!M.availability_sectiontime.form.addedEvents) {
        M.availability_sectiontime.form.addedEvents = true;
        var updateForm = function(input) {
            var ancestorNode = input.ancestor('span.availability_sectiontime');
            M.core_availability.form.update();
        };
        var root = Y.one('.availability-field');
        root.delegate('change', function() {
             updateForm(this);
        }, '.availability_sectiontime select');
        root.delegate('change', function() {
             updateForm(this);
        }, '.availability_sectiontime input[name=timespent]');
    }

    return node;
};

// This brings back form values into an exportable object
M.availability_sectiontime.form.fillValue = function(value, node) {
    // Set field.
    var field = node.one('select[name=sectionid]').get('value');
    if (field.substr(0, 2) === 's_') {
        value.s = field.substr(2);
    }

    var valueNode = node.one('input[name=timespent]');
    value.t = valueNode.get('value');
};

M.availability_sectiontime.form.fillErrors = function(errors, node) {

    var value = {};
    this.fillValue(value, node);

    // Check timespent
    if (value.t === undefined) {
        errors.push('availability_sectiontime:error_nulltimespent');
    }
    // Check section
    if (value.s === undefined) {
        errors.push('availability_sectiontime:error_nosection');
    }
};

}, '@VERSION@', {"requires": ["base", "node", "event", "io", "moodle-core_availability-form"]});
