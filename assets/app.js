const pointBalanceEl = document.getElementById('pointBalance');
const toastEl = document.getElementById('liveToast');
const toastBody = document.getElementById('toastBody');
const withdrawBtn = document.getElementById('withdrawBtn');
const submitWithdraw = document.getElementById('submitWithdraw');
const taskButtons = document.querySelectorAll('.task-btn');

if (toastEl && toastBody) {
  const toast = new bootstrap.Toast(toastEl, { delay: 2400 });
  const hasWithdraw = withdrawBtn && submitWithdraw && document.getElementById('withdrawModal');
  const withdrawModal = hasWithdraw ? new bootstrap.Modal('#withdrawModal') : null;
  let currentPoints = Number(window.initialPoints ?? 0);

  const formatPoints = (num) => new Intl.NumberFormat('id-ID').format(num);

  const showToast = (message) => {
    toastBody.textContent = message;
    toast.show();
  };

  const animateBalance = (target) => {
    if (!pointBalanceEl) {
      currentPoints = target;
      return;
    }

    const start = currentPoints;
    const duration = 500;
    const startTime = performance.now();

    const step = (time) => {
      const progress = Math.min((time - startTime) / duration, 1);
      const value = Math.floor(start + (target - start) * progress);
      pointBalanceEl.textContent = formatPoints(value);

      if (progress < 1) {
        requestAnimationFrame(step);
        return;
      }

      currentPoints = target;
    };

    requestAnimationFrame(step);
  };

  taskButtons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const reward = Number(btn.dataset.reward);
      const task = btn.dataset.task;

      btn.disabled = true;
      btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Proses';

      setTimeout(() => {
        animateBalance(currentPoints + reward);
        showToast(`✅ ${task} selesai! +${formatPoints(reward)} poin masuk.`);
        btn.innerHTML = '<i class="fa-solid fa-check me-1"></i>Selesai';
        btn.classList.remove('btn-gradient');
        btn.classList.add('btn-success');
      }, 900);
    });
  });

  if (hasWithdraw) {
    withdrawBtn.addEventListener('click', () => {
      withdrawModal.show();
    });

    submitWithdraw.addEventListener('click', () => {
      const danaNumber = document.getElementById('danaNumber').value.trim();
      const amount = Number(document.getElementById('withdrawAmount').value);

      if (!/^08\d{8,11}$/.test(danaNumber)) {
        showToast('⚠️ Nomor DANA tidak valid. Gunakan format 08xxxxxxxxxx');
        return;
      }

      if (!amount || amount < 15000) {
        showToast('⚠️ Minimal penarikan adalah 15.000 poin.');
        return;
      }

      if (amount > currentPoints) {
        showToast('⚠️ Poin kamu belum cukup untuk nominal tersebut.');
        return;
      }

      animateBalance(currentPoints - amount);
      withdrawModal.hide();
      showToast(`💸 Permintaan withdraw ${formatPoints(amount)} poin ke ${danaNumber} diproses.`);
    });
  }

  setTimeout(() => {
    showToast('Halo! Kerjakan misi kamu sekarang 🚀');
  }, 700);
}
