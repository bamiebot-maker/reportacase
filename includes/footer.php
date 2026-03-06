        </div><!-- /#wrapper -->
    </div><!-- /.container -->

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>

    <script>
    // Notification system
    function loadNotifications() {
        $.ajax({
            url: 'ajax/notifications.php',
            type: 'GET',
            success: function(response) {
                $('#notificationList').html(response);
                updateNotificationCount();
            },
            error: function() {
                $('#notificationList').html('<li><a class="dropdown-item text-center" href="#">Error loading notifications</a></li>');
            }
        });
    }

    function updateNotificationCount() {
        $.ajax({
            url: 'ajax/get_notification_count.php',
            type: 'GET',
            success: function(count) {
                if (count > 0) {
                    $('#notificationCount').text(count).show();
                } else {
                    $('#notificationCount').hide();
                }
            }
        });
    }

    // Auto-refresh notifications every 30 seconds
    setInterval(loadNotifications, 30000);
    
    // Load notifications when page loads
    $(document).ready(function() {
        loadNotifications();
        
        // Initialize DataTables
        $('.data-table').DataTable({
            "pageLength": 25,
            "responsive": true
        });

        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut(400);
        }, 50000);

        // Confirm delete actions
        $('.confirm-delete').on('click', function() {
            return confirm('Are you sure you want to delete this item? This action cannot be undone.');
        });
    });

    // Mark notification as read
    function markNotificationRead(notificationId) {
        $.ajax({
            url: 'ajax/mark_notification_read.php',
            type: 'POST',
            data: { notification_id: notificationId },
            success: function() {
                loadNotifications();
            }
        });
    }
    </script>
</body>
</html>