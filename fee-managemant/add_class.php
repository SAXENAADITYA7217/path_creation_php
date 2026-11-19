<?php
include('authentication.php');
include('includes/header.php');
?>

<?php include('message.php'); ?>

<div class="container-fluid px-1">
    <div class="row mt-1">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header mb-0">
                    <h5>Add Class
                        <a href="javascript:void(0);" class="btn btn-primary btn-sm float-end" id="addButton">Add Row</a>
                    </h5>
                </div>
                <div class="card-body mb-0">
                    <form action="master_code.php" method="POST">
                        <div class="row" id="classTypeContainer">

                            <!-- Initially No Input Fields are Present -->
                            
                        </div>

                        <div class="col-md-12 mb-3">
                            <button type="submit" name="add_class_Data" class="btn btn-primary btn-sm">Add Class Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let classTypeCount = 0;

    function addclassTypeBox() {
        classTypeCount++;
        const container = document.getElementById('classTypeContainer');

        const newField = document.createElement('div');
        newField.classList.add('col-md-6', 'mb-2');
        newField.id = 'classTypeField' + classTypeCount;

        newField.innerHTML = `
            <label>Class Name</label>

            <div class="d-flex align-items-center gap-2">
                <input type="text" 
                    name="class_type_name[]" 
                    class="form-control form-control-sm"
                    placeholder="Enter Class"
                    required>

                <button type="button" 
                    class="btn btn-sm btn-light p-1"
                    onclick="removeFeeType(${classTypeCount})"
                    style="border-radius:50%; width:28px; height:28px;">
                    &times;
                </button>
            </div>
        `;

        container.appendChild(newField);
    }

    function removeFeeType(id) {
        const field = document.getElementById('classTypeField' + id);
        if (field) field.remove();
    }

    document.getElementById('addButton').addEventListener('click', addclassTypeBox);

    window.onload = function() {
        addclassTypeBox();
    };

</script>


<?php
include('includes/footer.php');
include('includes/scripts.php');
?>
