<?php
//functions/views/rentals.php//
include_once 'functions/connection.php';

$sql = 'SELECT b.*, r.rent, DATEDIFF(DATE_ADD(b.start_date, INTERVAL 1 MONTH), CURDATE()) AS days_due FROM `boarders` b
        INNER JOIN `rooms` r ON b.room = r.id';

$stmt = $db->prepare($sql);
$stmt->execute();
$results = $stmt->fetchAll();

foreach ($results as $row) {
    $daysDue = $row['days_due'];
    $class = '';
    $text = '';
    $total = 0;
    if ($daysDue > 0) {
        $class = 'bg-success';
        $text = 'Due in ' . $daysDue . ' days';
    } elseif ($daysDue == 0) {
        $class = 'bg-warning';
        $text = 'Due Today';
        $total = round($row['rent'] + ($row['rent'] * abs($daysDue / 30)) );
    } else {
        $class = 'bg-danger';
        $text = 'Overdue ';
        $total = round($row['rent'] + ($row['rent'] * abs($daysDue / 30)) );
    }
?>
    <tr>
        <td><img class="rounded-circle me-2" width="30" height="30" src="functions/<?= $row['profile_picture'] ?>"><?= $row['fullname'] ?></td>
        <td>Room #<?= $row['room'] ?></td>
        <td>₱<?= $row['rent'] ?></td>
        <td>₱<?= number_format($total,2) ?></td>
        <td><?= $row['start_date'] ?></td>
        <td class="<?= $class ?>"><?= $text ?></td>
        <td><?= abs($daysDue > 0 ? 0 : $daysDue)?></td>
        <td class="text-center">
            <!-- Button to open modal -->
<a class="btn btn-primary" href="#" data-bs-toggle="modal" data-bs-target="#pay" 
   data-id="<?= $row['id'] ?>" data-room="<?= $row['room'] ?>" data-total="<?= $total ?>">
   <i class="far fa-money-bill-alt"></i>&nbsp;Payment
</a>

    <!-- Payment Modal -->
    <div class="modal fade" id="pay" tabindex="-1" aria-labelledby="payLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form method="POST" action="functions/payment.php">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="payLabel">Process Payment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
            <input type="hidden" name="id" id="modal_id">
            <input type="hidden" name="room" id="modal_room">
            <input type="hidden" name="total" id="modal_total">
            <div class="mb-3">
                <label for="amount" class="form-label">Amount Paid (₱)</label>
                <input type="number" name="amount" class="form-control" required>
            </div>
            <div><strong>Total Due: ₱<span id="modal_total_display"></span></strong></div>
            </div>
            <div class="modal-footer">
            <button type="submit" class="btn btn-success">Pay</button>
            </div>
        </div>
        </form>
    </div>
    </div>
        </td>
    </tr>
    <script>
        document.querySelectorAll('a[data-bs-target="#pay"]').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const room = this.getAttribute('data-room');
                const total = this.getAttribute('data-total');

                document.getElementById('modal_id').value = id;
                document.getElementById('modal_room').value = room;
                document.getElementById('modal_total').value = total;
                document.getElementById('modal_total_display').innerText = parseFloat(total).toFixed(1);
            });
        });
    </script>
<?php
}
?>
