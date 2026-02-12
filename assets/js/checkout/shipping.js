import { api } from './api.js';
import { State } from './state.js';

export async function loadShippingMethods() {
  return api('nailedit_checkout_shipping_methods', {}, { State });
}

export async function saveShipping() {
  if (!State.shipping) {
    throw new Error('Tarneviis pole valitud');
  }
  
  console.log('saveShipping - State.omnivaLocation:', State.omnivaLocation);
  console.log('saveShipping - State.smartpostLocation:', State.smartpostLocation);
  
  const payload = {
    shipping_method: State.shipping,
  };
  
  // Add pickup location if Omniva or Smartpost is selected
  const pickupLocation = State.omnivaLocation || State.smartpostLocation;
  console.log('saveShipping - pickupLocation:', pickupLocation);
  
  if (pickupLocation) {
    payload.pickup_location = JSON.stringify(pickupLocation);
    console.log('saveShipping - payload with pickup_location:', payload);
  } else {
    console.warn('saveShipping - NO pickup location found!');
  }
  
  return api('nailedit_checkout_save_shipping', payload, { State });
}

export async function loadOmnivaLocations() {
  return api('nailedit_omniva_locations');
}
