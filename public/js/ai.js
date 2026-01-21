document.addEventListener('DOMContentLoaded', function () {

    const aiBtn = document.getElementById('refresh-ai');
    const aiStatus = document.getElementById('ai-status');
    const aiText = document.getElementById('ai-analysis-text');

    if (!aiBtn || !aiStatus || !aiText) {
        console.warn('AI elements not found');
        return;
    }

    function formatAIText(text) {
        return text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/^\s*\*\s+(.*)$/gm, '<li>$1</li>')
            .replace(/(<li>.*<\/li>)/gs, '<ul>$1</ul>')
            .replace(/\n{2,}/g, '<br><br>');
    }

    aiBtn.addEventListener('click', async function () {
        aiBtn.disabled = true;
        aiStatus.className = 'badge bg-warning';
        aiStatus.innerText = 'Menganalisis...';

        try {
            const res = await fetch('/api/ai/analyze');
            const data = await res.json();

            console.log('RAW AI:', data.analysis);

            aiText.innerHTML = formatAIText(data.analysis);

            aiStatus.className = 'badge bg-success';
            aiStatus.innerText = 'Berhasil';

        } catch (err) {
            console.error(err);
            aiStatus.className = 'badge bg-danger';
            aiStatus.innerText = 'Gagal';
        } finally {
            aiBtn.disabled = false;
        }
    });

});