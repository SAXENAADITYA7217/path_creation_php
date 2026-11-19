<?php
include('authentication.php');
include('includes/header.php');

// Fetch classes
$academy_class_query = "SELECT * FROM erp_class_data WHERE class_status = '0' ORDER BY class_id ASC";
$result_academy_class = mysqli_query($con, $academy_class_query);

// Fetch fee types
$academy_fee_query = "SELECT * FROM erp_fee_type_detail ORDER BY fee_type_id ASC";
$result_academy_fee = mysqli_query($con, $academy_fee_query);

// Fetch Session
$academy_session_query = "SELECT * FROM erp_session WHERE session_status = '0' ORDER BY id DESC";
$result_session = mysqli_query($con, $academy_session_query);

// Store class options
$class_options = "";
mysqli_data_seek($result_academy_class, 0);
while ($row_class = mysqli_fetch_assoc($result_academy_class)) {
    $class_options .= "<option value='{$row_class['class_id']}'>{$row_class['class_name']}</option>";
}

// Store fee type options
$fee_type_options = "";
mysqli_data_seek($result_academy_fee, 0);
while ($row_fee_type = mysqli_fetch_assoc($result_academy_fee)) {
    $fee_type_options .= "<option value='{$row_fee_type['fee_type_id']}'>{$row_fee_type['fee_type_name']}</option>";
}

// Store session options
$session_option = "";
mysqli_data_seek($result_session, 0);
while ($row_session = mysqli_fetch_assoc($result_session)) {
    $session_option .= "<option value='{$row_session['session_id']}'>{$row_session['session_name']}</option>";
}
?>

<?php include('message.php'); ?>

<form action="master_code.php" method="POST">

<div class="container-fluid px-1">

    <div class="row mt-1">
        <div class="col-md-12">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mt-1">
                        Class Fees
                        <a href="javascript:void(0);" class="btn btn-primary btn-sm float-end" id="addCardBtn">Add Card</a>
                    </h5>
                </div>
            </div>
        </div>
    </div>

    <!-- CARDS CONTAINER -->
    <div id="cardsContainer"></div>

    <!-- TEMPLATE CARD -->
    <div id="cardTemplate" style="display:none;">
        <div class="card mb-3 fee-card" data-card-index="__index__">
            <div class="card-header">
                <h5>Add Class Fee
                    <button type="button" class="btn btn-danger btn-sm float-end remove-card">Remove Card</button>
                </h5>
            </div>

            <div class="card-body">

                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>Select Class</label>
                        <select name="class_id[__index__]" class="form-control form-control-sm fee-select class-select">
                            <option value="">-- Select Class --</option>
                            <?= $class_options; ?>
                        </select>
                    </div>

                    <div class="col-md-3 mt-4">
                        <button type="button" class="btn btn-primary btn-sm addRowBtn">Add Row</button>
                    </div>
                </div>

                <table class="table table-bordered fee-table">
                    <thead>
                        <tr>
                            <th>Fee Type</th>
                            <th>Fee Amount</th>
                            <th>Session</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody></tbody>
                </table>

            </div>
        </div>
    </div>

    <div class="col-md-12 mb-3 mt-3">
        <div class="card mb-3">
            <div class="card-header">
                <button type="submit" name="add_class_fee_Data" class="btn btn-success btn-sm">
                    Submit Class Fee Data
                </button>
            </div>
        </div>
    </div>

</div>
</form>

<script>
$(document).ready(function () {

    let cardIndex = 0;

    // FUNCTION TO CREATE ROW
    function getRowHtml(index) {
        return `
        <tr>
            <input type="hidden" name="class_index[]" value="${index}">
            <td>
                <select name="fee_type_id[]" class="form-control form-control-sm fee-select">
                    <option value="">--Select Fee Type--</option>
                    <?= $fee_type_options; ?>
                </select>
            </td>

            <td><input type="text" name="fee_amount[]" class="form-control form-control-sm"></td>

            <td>
                <select name="session_id[]" class="form-control form-control-sm fee-select">
                    <?= $session_option; ?>
                </select>
            </td>

            <td><button type="button" class="btn btn-danger btn-sm remove-row">X</button></td>
        </tr>`;
    }

    // FUNCTION TO ADD CARD
    function addNewCard() {
        let template = $("#cardTemplate").html();
        template = template.replace(/__index__/g, cardIndex);

        $("#cardsContainer").append(template);

        let newCard = $(`.fee-card[data-card-index="${cardIndex}"]`);

        newCard.find("tbody").append(getRowHtml(cardIndex));
        newCard.find(".fee-select").select2({ width: "100%" });

        cardIndex++;
    }

    // LOAD FIRST CARD AUTOMATICALLY
    addNewCard();

    // ADD CARD BUTTON
    $("#addCardBtn").click(function () {
        addNewCard();
    });

    // ADD ROW BUTTON
    $(document).on("click", ".addRowBtn", function () {
        let index = $(this).closest(".fee-card").data("card-index");
        let cardBody = $(this).closest(".fee-card");

        cardBody.find("tbody").append(getRowHtml(index));
        cardBody.find("tbody tr:last .fee-select").select2({ width: "100%" });
    });

    // REMOVE ROW
    $(document).on("click", ".remove-row", function () {
        if ($(this).closest("tbody").find("tr").length == 1) {
            alert("At least one row is required!");
            return;
        }
        $(this).closest("tr").remove();
    });

    // REMOVE CARD
    $(document).on("click", ".remove-card", function () {
        $(this).closest(".fee-card").remove();
    });

});
</script>

<?php 
include('includes/footer.php'); 
include('includes/scripts.php'); 
?>
