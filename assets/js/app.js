// Cálculo em tempo real da taxa e do valor líquido (a validação real é feita no servidor)
document.querySelectorAll('form.invest').forEach(f => {
  const upd = () => {
    const amt = parseFloat((f.amount.value || '0').replace(',', '.')) || 0;
    const fee = parseFloat(f.broker_id.selectedOptions[0]?.dataset.fee || 0);
    const t = amt * fee / 100;
    f.querySelector('.calc').textContent = 'Taxa: ' + t.toFixed(2) + ' Kz · Líquido: ' + (amt - t).toFixed(2) + ' Kz';
  };
  f.amount.addEventListener('input', upd); f.broker_id.addEventListener('change', upd); upd();
});
