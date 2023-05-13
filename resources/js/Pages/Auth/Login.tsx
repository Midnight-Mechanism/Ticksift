import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect } from 'react';

import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import Layout from '@/Layouts/Layout';

export default function Login({ status, canResetPassword }: { status: string; canResetPassword: boolean }) {
  const { data, setData, post, processing, errors, reset } = useForm({
    email: '',
    password: '',
    remember: '',
  });

  useEffect(() => {
    return () => {
      reset('password');
    };
  }, []);

  const onHandleChange = (event: any) => {
    setData(event.target.name, event.target.type === 'checkbox' ? event.target.checked : event.target.value);
  };

  const submit = (e: any) => {
    e.preventDefault();

    post(route('login'));
  };

  return (
    <Layout title="Log in">
      <div className="mx-auto max-w-lg px-4 sm:px-6 lg:px-8">
        {status && <div className="mb-4 font-medium text-sm text-green-300">{status}</div>}

        <form onSubmit={submit}>
          <div>
            <InputLabel forInput="email" value="Email" />

            <TextInput
              type="text"
              name="email"
              value={data.email}
              className="mt-1 block w-full"
              autoComplete="username"
              isFocused={true}
              handleChange={onHandleChange}
            />

            <InputError message={errors.email} className="mt-2" />
          </div>

          <div className="mt-4">
            <InputLabel forInput="password" value="Password" />

            <TextInput
              type="password"
              name="password"
              value={data.password}
              className="mt-1 block w-full"
              autoComplete="current-password"
              handleChange={onHandleChange}
            />

            <InputError message={errors.password} className="mt-2" />
          </div>

          <div className="flex items-center justify-end mt-4">
            {canResetPassword && (
              <Link href={route('password.request')} className="underline text-sm hover:text-blue-100">
                Forgot your password?
              </Link>
            )}

            <PrimaryButton className="ml-4" processing={processing}>
              Log in
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Layout>
  );
}
