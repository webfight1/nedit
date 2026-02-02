export async function addressFlow({
  ui,
  saveAddress,
  refreshShippingMethods,
  updateShippingUI,
  markStepComplete,
  advanceStep,
  getString,
}, form) {
  ui.showLoader();
  try {
    const addressResponse = await saveAddress(form);
    
    let shippingMethods = [];
    if (addressResponse?.data?.rates || addressResponse?.data?.data?.rates) {
      shippingMethods = updateShippingUI?.(addressResponse) || [];
    }
    
    if (!shippingMethods.length) {
      shippingMethods = await refreshShippingMethods();
    }
    
    if (!shippingMethods.length) {
      throw new Error(getString('shippingNotFound', 'Tarneviise ei leitud.'));
    }
    markStepComplete('address');
    advanceStep('address');
  } finally {
    ui.hideLoader();
  }
}
