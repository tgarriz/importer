    <!DOCTYPE html>
    <html lang="en">
    <head>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" crossorigin="anonymous">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" crossorigin="anonymous">
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" crossorigin="anonymous"></script>
	<script src="jss/validadores.js"></script>
    </head>
    <body>
        <div id="wrap">
            <div class="container">
                <div class="row">
                    <form class="form-horizontal" action="functions.php" method="post" name="upload_excel" enctype="multipart/form-data">
                        <fieldset>
                            <!-- Form Name -->
                            <legend>Formulario Importador</legend>
                            <!-- File Button -->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="filebutton">Select File</label>
                                <div class="col-md-4">
                                    <input type="file" name="file" id="file" onchange="triggerValidation(this)" class="input-large">
                                </div>
                            </div>
                            <!-- Button -->
                            <div class="form-group">
                                <label class="col-md-4 control-label" for="singlebutton">Import data</label>
                                <div class="col-md-4">
                                    <button type="submit" id="submit_import" name="Import" class="btn btn-primary button-loading" data-loading-text="Loading...">Importar!</button>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" id="submit_generar" name="Generar" class="btn btn-primary button-loading" data-loading-text="Loading...">Generar!</button>
                                </div>
                            </div>
                        </fieldset>
                    </form>
                </div>
		<?php
		   include 'config.php';
                   get_all_records();
                ?>
            </div>
        </div>
    </body>
    </html>
