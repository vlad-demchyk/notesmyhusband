import gql from 'graphql-tag'
import { apolloClient } from '../hooks/apolloClient'

export function authService() {
  const login = async (credentials: { login: string; password: string; email?: string }) => {
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
    const result = await apolloClient.mutate({
      mutation,
      variables: credentials,
    })

    console.log('Login result:', result)
    return result.data.login
  }

  const register = async (credentials: { login: string; password: string; email: string }) => {
    const mutation = gql`
      mutation Register($login: String!, $password: String!, $email: String!) {
        register(login: $login, password: $password, email: $email) {
          token
          user {
            id
            login
            created_at
          }
        }
      }
    `
    const result = await apolloClient.mutate({
      mutation,
      variables: credentials,
    })

    console.log('register result:', result)
    return result.data.register
  }

  const getCurrentUser = async () => {
    const query = gql`
      query GetUser {
        me {
          id
          login
          created_at
        }
      }
    `
    const result = await apolloClient.query({
      query,
      fetchPolicy: 'network-only',
    })

    console.log('GetUser result:', result)
    return result.data.me
  }

  return {
    login,
    register,
    getCurrentUser,
  }
}
