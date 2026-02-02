const companyToggle = document.getElementById('billing_is_company');
const companyFields = document.getElementById('nailedit-company-fields');
const companyRequiredIds = [
  'billing_company_name',
  'billing_company_reg',
  'billing_address',
  'billing_city',
  'billing_state',
  'billing_postcode',
];

export function initAddressUI() {
  if (!companyToggle || !companyFields) return;

  const setRequired = (required) => {
    companyRequiredIds.forEach((id) => {
      const el = document.getElementById(id);
      if (el) {
        el.required = required;
      }
    });
  };

  const toggle = () => {
    if (companyToggle.checked) {
      companyFields.classList.remove('hidden');
      setRequired(true);
    } else {
      companyFields.classList.add('hidden');
      setRequired(false);
    }
  };

  companyToggle.addEventListener('change', toggle);
  toggle();
}
