import { configure, defineRule } from 'vee-validate';

// Define basic validation rules manually without @vee-validate/rules
defineRule('required', (value) => {
  if (!value || !value.length) {
    return 'این فیلد الزامی است';
  }
  return true;
});

defineRule('email', (value) => {
  if (!value || !value.length) {
    return true;
  }
  // Simple email validation regex
  const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  if (!emailRegex.test(value)) {
    return 'ایمیل وارد شده معتبر نیست';
  }
  return true;
});

defineRule('min', (value, [limit]) => {
  if (!value || !value.length) {
    return true;
  }
  if (value.length < limit) {
    return `این فیلد باید حداقل ${limit} کاراکتر باشد`;
  }
  return true;
});

defineRule('confirmed', (value, [target]) => {
  if (value === target) {
    return true;
  }
  return 'تکرار رمز عبور مطابقت ندارد';
});

// Custom messages configuration
configure({
  generateMessage: (context) => {
    const messages = {
      required: 'این فیلد الزامی است',
      email: 'ایمیل وارد شده معتبر نیست',
      min: `این فیلد باید حداقل ${context.rule.params[0]} کاراکتر باشد`,
      confirmed: 'تکرار رمز عبور مطابقت ندارد'
    };

    return messages[context.rule.name] || `مقدار وارد شده برای ${context.field} معتبر نیست`;
  }
});

export default {
  install(app) {
    // Any additional setup if needed
  }
}; 