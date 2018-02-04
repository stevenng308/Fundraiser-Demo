/*
* This file handles the form submit with JS validation on the form data. If there are any issues an alert is fired.
*/
$(document).ready(function(){
    function validateEmail(email)
    {
        var regex = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;

        return regex.test(email.toLowerCase());
    }

    function validateRating(num)
    {
        var regex = /([1-5])/;
        return regex.test(num);
    }

    function validateLength(str, length)
    {
        return (str.length <= length) ? true : false;
    }

    $('button#submit').on('click', function()
    {
        var formBox = {
            "name"            : "Please enter a name up to 50 characters.",
            "email"           : "Please enter an email address up to 50 characters.",
            "rating"          : "Please enter a rating between 1 - 5.",
            "message"         : "Please enter a review message up to 500 characters.",
            "fundraiser_name" : "Please enter a fundraiser name up to 50 characters."
        };

        var fundraiserId    = $("form#reviewForm input#fundraiser-id").val();
        var formData        = {};
        formData["name"]    = $("form#reviewForm input#reviewer-name").val();
        formData["email"]   = $("form#reviewForm input#reviewer-email").val();
        formData["rating"]  = $("form#reviewForm select option:selected").val();
        formData["message"] = $("form#reviewForm textarea#reviewer-text").val();
        if(fundraiserId === "new")
        {
            formData["fundraiser_name"] = $("form#reviewForm input#fundraiser-name").val(); //this is a "fundraiser" that a user is adding a review for. There is backend logic to check if they are using this to add duplicate reviews
        }
        if(!validateRating(formData["rating"]))
        {
            alert(formBox["rating"]);
            return false;
        } else if(!validateEmail(formData["email"]))
        {
            alert("Please enter a valid email address.");
            return false;
        } else {
            for(label in formData)
            {
                var flag = false;
                switch(label)
                {
                    case "name":
                        flag = validateLength(formData[label], 50);
                        break;
                    case "email":
                        flag = validateLength(formData[label], 50);
                        break;
                    case "rating":
                        flag = validateLength(formData[label], 1);
                        break;
                    case "message":
                        flag = validateLength(formData[label], 500);
                        break;
                    case "fundraiser_name":
                        flag = validateLength(formData[label], 50);
                        break;
                }
                if(!flag)
                {
                    alert(formBox[label]);
                    return false;
                }
            }
        }
        var dataUrl         = "/controllers/mainController.php/submit/" + fundraiserId;
        $.ajax({
            url: dataUrl,
            type: "POST",
            datatype: JSON,
            data: formData,

            success: function(data){
                data = $.parseJSON(data);
                // console.log(data);
                alert(data.msg);
                if(data.status === "pass")
                {
                    window.location = redirectUrl;
                }
            },
            error: function(){
                alert("Your review cannot be processed.");
            }
        });
    });

});
