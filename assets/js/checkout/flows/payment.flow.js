export async function paymentFlow({
  savePayment,
  markStepComplete,
  advanceStep,
}) {
  await savePayment();
  markStepComplete('payment');
  advanceStep('payment');
}
