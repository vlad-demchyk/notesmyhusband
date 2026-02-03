import gql from 'graphql-tag'
import { apolloClient } from './apolloClient'

/**
 * API Layer - тільки виклики GraphQL
 * Відповідальність:
 * - Формування GraphQL запитів
 * - Виклик Apollo Client
 * - Базова обробка помилок (тільки для логування)
 * - Повернення даних або викидання помилок
 * 
 * НЕ управляє станом, НЕ зберігає токени, НЕ обробляє помилки для UI
 */
export function useAuthActions() {
  const login = async (credentials: { login: string; password: string, email?: string }) => {
    const mutation = gql`
      mutation Login($login: String!, $password: String!) {
        login(login: $login, password: $password) {
          token
          user {
            id
            login
            created_at
          }
        }
      }
    `

    try {
      const response = await apolloClient.mutate({
        mutation,
        variables: {
          login: credentials.login,
          password: credentials.password
        }
      })

      // Базова перевірка відповіді
      if (!response.data?.login) {
        const errorMessage = response.errors?.[0]?.message
        throw new Error(errorMessage)
      }
      
      // Повертаємо дані без збереження токена (це робить store)
      return {
        user: response.data.login.user,
        token: response.data.login.token,
      }
    } catch (error: any) {
      // Базова обробка помилок - тільки для логування
      // Детальна обробка для UI - в store
      const errorMessage = error?.graphQLErrors?.[0]?.message || error?.message || 'Помилка входу'
      console.error('[useAuthActions] Login error:', errorMessage)
      throw new Error(errorMessage)
    }
  }

  const logout = async () => {
    // Sanctum: клієнт просто видаляє токен; серверну ревізію можна додати окремо
    localStorage.removeItem('user_token')
  }

  const getCurrentUser = async () => {
    const token = localStorage.getItem('user_token')
    if (!token) {
      return null
    }

    const query = gql`
      query Me {
        me {
          id
          login
          email
          created_at
        }
      }
    `

    try {
      // Токен автоматично додається через authLink в apolloClient.ts
      // Не потрібно передавати його в variables
      const response = await apolloClient.query({
        query,
        fetchPolicy: 'network-only',
        errorPolicy: 'all' // Не викидає помилку при GraphQL errors, повертає їх в response.errors
      })

      // Перевіряємо наявність помилок авторизації
      if (response.errors && response.errors.length > 0) {
        const authError = response.errors.find(err => 
          err.message?.includes('Unauthenticated') || 
          err.message?.includes('Unauthorized') ||
          err.extensions?.category === 'authentication'
        )
        if (authError) {
          // Токен невалідний
          return null
        }
      }

      if (!response.data?.me) {
        return null
      }

      return response.data.me
    } catch (error: any) {
      // Перевіряємо тип помилки
      if (error?.graphQLErrors) {
        const authError = error.graphQLErrors.find((err: any) => 
          err.message?.includes('Unauthenticated') || 
          err.message?.includes('Unauthorized')
        )
        if (authError) {
          // Токен невалідний
          return null
        }
      }
      
      // Інші помилки (мережа тощо) - не видаляємо токен, можливо тимчасовий збій
      console.error('[useAuthActions] Get user error:', error)
      return null
    }
  }

  const registerUser = async (credentials: { login: string; password: string, email?: string }): Promise<{ user: { id: number, login: string, email?: string, created_at: string }, token: string }> => {
    const mutation = gql`
      mutation Register($login: String!, $password: String!, $email: String) {
        register(login: $login, password: $password, email: $email) {
          token
          user {
            id
            login
            email
            created_at
          }
        }
      }
    `
    try {
      const response = await apolloClient.mutate({
        mutation,
        variables: {
          login: credentials.login,
          password: credentials.password,
          email: credentials.email
        }
      })

      // Базова перевірка відповіді
      if (!response.data?.register) {
        const errorMessage = response.errors?.[0]?.message || 'Помилка реєстрації'
        throw new Error(errorMessage)
      }
      
      // Повертаємо дані без збереження токена (це робить store)
      return {
        user: response.data.register.user,
        token: response.data.register.token,
      }
    } catch (error: any) {
      // Базова обробка помилок - тільки для логування
      // Детальна обробка для UI - в store
      let errorMessage = 'Помилка реєстрації'
      
      if (error?.graphQLErrors && error.graphQLErrors.length > 0) {
        errorMessage = error.graphQLErrors[0].message || errorMessage
      } else if (error?.networkError) {
        errorMessage = error.networkError.message || errorMessage
      } else if (error?.message) {
        errorMessage = error.message
      }
      
      console.error('[useAuthActions] Register error:', errorMessage)
      throw new Error(errorMessage)
    }
  }
  return { login, logout, getCurrentUser, registerUser }
}
