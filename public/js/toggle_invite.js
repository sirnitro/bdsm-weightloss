<script>
document.querySelectorAll('.toggle-invite').forEach(btn => {
    btn.addEventListener('click', function () {
        const inviteId = this.dataset.id;

        fetch('toggle_invite.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `invite_id=${inviteId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.getElementById(`invite-${inviteId}`);
                row.querySelector('.status').textContent = data.new_status ? 'Revoked' : 'Active';
                this.textContent = data.new_status ? 'Restore' : 'Revoke';
            }
        });
    });
});
</script>

