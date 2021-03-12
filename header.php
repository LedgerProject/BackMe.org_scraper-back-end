<?php
session_start();
$baseURL = '/aletheia/';
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<meta name="author" content="Oxjno Web Development - https://oxjno.com">
		<title>Aletheia</title>

		<!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
		<link href="<?php echo $baseURL; ?>css/bootstrap.min.css" rel="stylesheet">
		<!-- Custom styles for this template -->
		<link href="<?php echo $baseURL; ?>css/style.css" rel="stylesheet">

        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
        <!-- Bootstrap JS -->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
        <!-- HTMLDIFF JS -->
        <script src="<?php echo $baseURL; ?>js/htmldiff.js"></script>
		
		<script>
            function highlight(newElem, oldElem){ 
                var oldText = oldElem.text(),     
                    text = '';
                newElem.text().split('').forEach(function(val, i){
                    if (val != oldText.charAt(i))
                    text += "<span class='highlight'>"+val+"</span>";  
                    else
                    text += val;            
                });
                newElem.html(text); 
            }
            $(document).ready(function () {
                $.fn.serializeObject = function()
                {
                    var o = {};
                    var a = this.serializeArray();
                    $.each(a, function() {
                        if (o[this.name] !== undefined) {
                            if (!o[this.name].push) {
                                o[this.name] = [o[this.name]];
                            }
                            o[this.name].push(this.value || '');
                        } else {
                            o[this.name] = this.value || '';
                        }
                    });
                    return o;
                };
                $("#form-search").submit(function(event){
                    event.preventDefault();
                    $('#founds').empty();
                    var post_url = $(this).attr("action");
                    var request_method = $(this).attr("method");
                    var form_data = $(this).serializeObject();
                    console.log(form_data);
                    $.ajax({
                        url : post_url,
                        type: request_method,
                        data : JSON.stringify(form_data),
                        dataType: 'json',
                        contentType: 'application/json;charset=UTF-8',
                        error: function(response) {
                            msg = 'Generic error';
                            if(response.responseJSON.result.msg != undefined){
                                msg = response.responseJSON.result.msg;
                            }
                            alert(msg);
                        }
                    }).done(function(response){ //
                        console.log('mhm',response);
                        if(response.result.status=='ok'){
                            console.log('response');
                            //window.location.href = '/result';
                            //$('#founds').append('<p>Content from: '+form_data.url+'</p>');
                            //$('#founds').append('<h3>'+response.result.article[0].title+'</h3>');
                            //response.result.article[0].content.forEach( (element, index) => {
                            //    $('#founds').append('<p>'+element+'</p>'); 
                            //});
                            window.location.href = './article/'+response.result.uid;

                        }else{
                            alert(response.result.msg);
                            console.log('response no');
                        }
                    });
                });
            });
        </script>

	</head>
	<body>
    <header class="container-fluid">
        <div class="row">
            <div class="col-6 col-md-1 offset-md-1 d-flex align-items-center"><a href="<?php echo $baseURL; ?>" class="d-block w-100"><img src="<?php echo $baseURL; ?>img/logo-aletheia.svg" alt="" class="w-100 my-3"></a></div>
            <div class="col-6 col-md-1 offset-md-8 d-flex align-items-center"><a href="https://backme.org" target="_blank" class="d-block w-100"><img src="<?php echo $baseURL; ?>img/logo-backme.svg" alt="" class="w-100 my-3"></a></div>
        </div>
    </header>

    <div class="container">
        
        <section class="row my-5">
            <div class="col-12 col-md-4">
            <img src="<?php echo $baseURL; ?>img/visual.svg" class="w-100" alt="">
            </div>
            <div class="col-12 col-md-8">
                <h2>Everyone is accountable.</h2>
                <p>
                Aletheia aims to track and fight fake news.<br>
                It is a tool for the public that allows in an easy way to check if/when/how a news has been edited.<br>
                Aletheia is one of the tools provided by <a href="https://backme.org" target="_blank">BackMe</a> for the freedom and the truth on the web.
                </p>
            </div>
        </section>

    </div>