    function submitFormAndValidate(argument) {
        if ($('#'+argument).parsley().validate()) {
            $('#'+argument).submit();
        }
    }
