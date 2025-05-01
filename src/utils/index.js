import { format as dateFnsFormat } from 'date-fns'

export const formatDate = (date, format = null) => {
  return dateFnsFormat(date, format ?? 'MMM d, yyyy') // Using a more complete date format
}

export const truncateText = (text = '', maxLength = 15) => {
  if (text && text.length > maxLength) {
    return text.substring(0, maxLength) + '...'
  }
  return text
}
export const toPercentage = (portion = 0, whole = 0, decimals = 2) => {
  if (whole === 0) return 0
  const percentage = (portion / whole) * 100
  return parseFloat(percentage.toFixed(decimals)) + '%'
}
export const formatCurrency = (number = 0) =>
  new Intl.NumberFormat('en-US', {
    style: 'decimal',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(number)
