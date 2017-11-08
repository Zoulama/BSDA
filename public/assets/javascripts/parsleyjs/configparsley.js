window.ParsleyConfig = {
    excluded: ' input[type=hidden], [disabled], :hidden ', // Do not validate input[type=hidden] & :disabled.
    errorClass: 'has-error',
    successClass: 'has-success',
    classHandler: function (ParsleyField) {
      return ParsleyField.$element.parents('.form-group');
    },
    errorsContainer: function (ParsleyField) {
      return ParsleyField.$element.parents('.form-group');
    },
    errorsWrapper: '<span class="help-block">',
    errorTemplate: '<div></div>'
};