export async function shippingFlow({
  ui,
  saveShipping,
  updateSummaryFromResult,
  refreshPaymentMethods,
  updatePaymentUI,
  markStepComplete,
  advanceStep,
  isOmnivaShipping,
  State,
}, method) {
  if (!method) {
    return;
  }

  const code = typeof method === 'string' ? method : method.code;
  if (!code) {
    return;
  }

  ui.showLoader();
  try {
    const result = await saveShipping();
    updateSummaryFromResult(result);
    
    let paymentMethodsLoaded = false;
    if (result?.data?.methods || result?.data?.data?.methods) {
      updatePaymentUI?.(result);
      paymentMethodsLoaded = true;
    }
    
    if (!paymentMethodsLoaded) {
      await refreshPaymentMethods();
    }
    
    if (!isOmnivaShipping(code) || State.omnivaLocation) {
      markStepComplete('shipping');
      advanceStep('shipping');
    }
  } catch (error) {
    console.error('Save shipping failed:', error);
  } finally {
    ui.hideLoader();
  }
}
