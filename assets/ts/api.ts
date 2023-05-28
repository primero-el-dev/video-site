import axios, { Method, AxiosResponse } from 'axios';
import { useAppStore } from './state';
import { BackendRoute, constructBackendUri } from './router';

export interface ApiResponse {}

export interface SuccessfulApiResponse extends ApiResponse {
  data?: any[],
  message?: string,
}

export interface FailedApiResponse extends ApiResponse {
  error?: string,
  errors?: { [name: string]: string },
}

let requestIsPending = false;
let requestWatchIntervalId = null;

async function waitUntil(condition: boolean) {
  return await new Promise(resolve => {
    const interval = setInterval(() => {
      if (condition) {
        resolve('foo');
        clearInterval(interval);
      };
    }, 1000);
  });
}

export async function requestApi(route: BackendRoute, data: any = null, options: any = {}): Promise<AxiosResponse> {
  try {
    let m: string = route.method;
    let method = m.toLowerCase() as unknown as (Method | null);
    
    if (!method) {
      throw Error(`Invalid axios HTTP method '${m}'.`);
    }

    // waitUntil()
    let response = await (axios as any)[method](constructBackendUri(route), data, options);
    
    useAppStore().checkUpdateIsLogged();
  
    return response;
  } catch (error) {
    useAppStore().checkUpdateIsLogged();

    throw error;
  }
}

export function getResponseError(e: Error): string {
  return axios.isAxiosError(e) 
    ? e!.response!.data!.error 
    : (e as Error).message;
}

// export async function requestApi(submitRoute: BackendRoute): Promise<void> {
//   try {
//     let m: string = submitRoute.method;
//     let method = m.toLowerCase() as unknown as (Method | null);
//     if (!method) {
//       throw Error(`Invalid axios HTTP method '${m}'.`);
//     }

//     let response = await (axios as any)[method](submitRoute.path);
//     if (!response.data) {
//       throw Error(`Invalid response data '${m}'.`);
//     }

//     if (response.data.message) {
//       alertStore().addSuccess(response.data.message);
//     }
//     router.push({ name: 'home' });
//   } catch (error) {
//     if (axios.isAxiosError(error)) {
//       if (!error.response) {
//         return;
//       }
//       if (error.response.data.error) {
//         this.error = error.response.data.error;
//       }
//       if (error.response.data.errors) {
//         this.errors = error.response.data.errors;
//       }
//     } else {
//       throw error;
//     }
//   }
// }