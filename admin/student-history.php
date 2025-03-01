<!-- Return Modal -->
<div class="modal fade" id="returnModal<?php echo $cnt; ?>" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title" id="returnModalLabel">Return Book</h4>
            </div>
            <div class="modal-body">
                <form role="form" method="get" action="manage-issued-books.php">
                    <input type="hidden" name="return" value="<?php echo htmlentities($book->BookId); ?>">
                    <input type="hidden" name="studentid" value="<?php echo htmlentities($studentId); ?>">
                    
                    <div class="form-group">
                        <label>Book Title</label>
                        <input class="form-control" type="text" value="<?php echo htmlentities($book->Title); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Student ID</label>
                        <input class="form-control" type="text" value="<?php echo htmlentities($studentId); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Issue Date</label>
                        <input class="form-control" type="text" value="<?php echo htmlentities(date('d-m-Y', strtotime($book->IssuesDate))); ?>" readonly>
                    </div>
                    
                    <div class="form-group">
                        <label>Return Date</label>
                        <input class="form-control" type="date" name="returndate" id="returndate<?php echo $cnt; ?>" value="<?php echo date('Y-m-d'); ?>" onchange="calculateFine<?php echo $cnt; ?>()" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Days Kept</label>
                        <input class="form-control" type="text" id="daysKept<?php echo $cnt; ?>" value="<?php echo $daysIssued; ?> days" readonly>
                    </div>
                    
                    <div id="overdueInfo<?php echo $cnt; ?>">
                        <?php if($isOverdue): ?>
                        <div class="alert alert-warning">
                            <p>This book is overdue by <?php echo ($daysIssued - 14); ?> days.</p>
                            <p>Fine rate: 5 RM per day after the 14-day period.</p>
                            <p>Calculated fine: <?php echo (($daysIssued - 14) * 5); ?> RM</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Fine (RM)</label>
                        <input class="form-control" type="number" name="fine" id="fine<?php echo $cnt; ?>" min="0" value="<?php echo $isOverdue ? (($daysIssued - 14) * 5) : 0; ?>" readonly>
                        <small class="text-muted">Fine is calculated at 5 RM per day after the 14-day period</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Override Fine (if needed)</label>
                        <div class="input-group">
                            <span class="input-group-addon">
                                <input type="checkbox" id="overrideFine<?php echo $cnt; ?>" onchange="toggleFineEdit<?php echo $cnt; ?>()">
                            </span>
                            <input type="number" class="form-control" id="manualFine<?php echo $cnt; ?>" min="0" disabled>
                        </div>
                        <small class="text-muted">Check this box to manually override the calculated fine</small>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Confirm Return</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for this specific modal -->
<script>
    // Calculate fine based on selected return date
    function calculateFine<?php echo $cnt; ?>() {
        // Get the issue date and selected return date
        var issueDate = new Date("<?php echo date('Y-m-d', strtotime($book->IssuesDate)); ?>");
        var returnDate = new Date(document.getElementById('returndate<?php echo $cnt; ?>').value);
        
        // Calculate days between dates
        var timeDiff = returnDate.getTime() - issueDate.getTime();
        var daysKept = Math.ceil(timeDiff / (1000 * 3600 * 24));
        
        // Update days kept field
        document.getElementById('daysKept<?php echo $cnt; ?>').value = daysKept + " days";
        
        // Calculate fine (5 RM per day after 14 days)
        var fine = 0;
        if (daysKept > 14) {
            fine = (daysKept - 14) * 5;
        }
        
        // Update fine field
        document.getElementById('fine<?php echo $cnt; ?>').value = fine;
        
        // Update overdue information
        var overdueInfo = document.getElementById('overdueInfo<?php echo $cnt; ?>');
        if (daysKept > 14) {
            overdueInfo.innerHTML = `
                <div class="alert alert-warning">
                    <p>This book is overdue by ${daysKept - 14} days.</p>
                    <p>Fine rate: 5 RM per day after the 14-day period.</p>
                    <p>Calculated fine: ${fine} RM</p>
                </div>
            `;
        } else {
            overdueInfo.innerHTML = `
                <div class="alert alert-success">
                    <p>This book is being returned within the 14-day period.</p>
                    <p>No fine will be charged.</p>
                </div>
            `;
        }
    }
    
    // Toggle between automatic and manual fine
    function toggleFineEdit<?php echo $cnt; ?>() {
        var checkbox = document.getElementById('overrideFine<?php echo $cnt; ?>');
        var fineField = document.getElementById('fine<?php echo $cnt; ?>');
        var manualFineField = document.getElementById('manualFine<?php echo $cnt; ?>');
        
        if (checkbox.checked) {
            // Enable manual override
            fineField.readOnly = true;
            manualFineField.disabled = false;
            manualFineField.value = fineField.value;
            
            // Add an event listener to update the actual fine field
            manualFineField.addEventListener('input', function() {
                fineField.value = this.value;
            });
        } else {
            // Disable manual override and recalculate
            fineField.readOnly = true;
            manualFineField.disabled = true;
            calculateFine<?php echo $cnt; ?>();
        }
    }
    
    // Run calculation when the modal is shown
    $('#returnModal<?php echo $cnt; ?>').on('shown.bs.modal', function() {
        calculateFine<?php echo $cnt; ?>();
    });
</script>