<div class="p-4 bg-white dark:bg-gray-800 rounded-lg shadow" style="min-height: 350px;">
    <canvas id="testCanvas" style="width: 400px; height: 300px; border: 2px solid blue; background-color: lightgray;"></canvas>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const ctx = document.getElementById('testCanvas').getContext('2d');
        if (ctx) {
            ctx.fillStyle = 'red';
            ctx.fillRect(0, 0, 100, 100); // Draw a red square

            ctx.fillStyle = 'blue'; // Change color for text
            ctx.font = '20px Arial';
            ctx.fillText('Canvas Works!', 10, 50); // Draw some text
        }
    });
</script>
