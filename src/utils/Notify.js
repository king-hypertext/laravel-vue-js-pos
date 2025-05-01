import { Notyf } from 'notyf'
import 'notyf/notyf.min.css' // for React, Vue and Svelte

// Create an instance of Notyf
const notyf = new Notyf({
  duration: 5000,
  dismissible: false,
  position: {
    x: 'center',
    y: 'top',
  },
  ripple: false,
  // types: [
  //     {
  //         type: 'warning',
  //         background: 'orange',
  //         icon: {
  //             className: 'material-icons',
  //             tagName: 'i',
  //             text: 'warning'
  //         }
  //     },
  //     {
  //         type: 'error',
  //         background: 'indianred',
  //         duration: 2000,
  //         dismissible: true
  //     }
  // ]
})

// Display an error notification
const Alert = {
  success: (message) => {
    notyf.success({
      message,
    })
  },
  error: (message) => {
    notyf.error({
      message,
    })
  },
  info: (message) => {
    notyf.info({
      message,
    })
  },
}
export default Alert
