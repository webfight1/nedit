export async function shippingFlow({
  ui,
  saveShipping,
  updateSummaryFromResult,
  refreshPaymentMethods,
  updatePaymentUI,
  markStepComplete,
  advanceStep,
  isOmnivaShipping,
  isSmartpostShipping,
  autoPaymentFlow,
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
    
    let methods = [];
    let paymentMethodsLoaded = false;
    if (result?.data?.methods || result?.data?.data?.methods) {
      methods = updatePaymentUI?.(result) || [];
      paymentMethodsLoaded = true;
    }
    
    if (!paymentMethodsLoaded) {
      methods = await refreshPaymentMethods() || [];
    }
    
    const needsOmniva = isOmnivaShipping(code) && !State.omnivaLocation;
    const needsSmartpost = isSmartpostShipping?.(code) && !State.smartpostLocation;
    if (!needsOmniva && !needsSmartpost) {
      markStepComplete('shipping');

      if (methods.length === 1 && typeof autoPaymentFlow === 'function') {
        await autoPaymentFlow();
      } else {
        advanceStep('shipping');
      }
    }
  } catch (error) {
    console.error('Save shipping failed:', error);
  } finally {
    ui.hideLoader();
  }
}
