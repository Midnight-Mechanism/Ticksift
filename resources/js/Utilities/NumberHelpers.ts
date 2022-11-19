export const formatCurrency = (number: number, code: string) => {
  if (!code) {
    return new Intl.NumberFormat('en-US', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(number);
  }
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: code,
  }).format(number);
};

export const getNumberWithOrdinal = (number: number) => {
  const suffixes = ['th', 'st', 'nd', 'rd'];
  const base = number % 100;
  return number + (suffixes[(base - 20) % 10] || suffixes[base] || suffixes[0]);
};
