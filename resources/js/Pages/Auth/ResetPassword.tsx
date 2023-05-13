import { useForm } from '@inertiajs/react';
import { useEffect } from 'react';

import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import Layout from '@/Layouts/Layout';

export default function ResetPassword({ token, email }: { token: string; email: string }) {
  const { data, setData, post, processing, errors, reset } = useForm({
    token: token,
    email: email,
    password: '',
    password_confirmation: '',
  });

  useEffect(() => {
    return () => {
      reset('password', 'password_confirmation');
    };
  }, []);

  const onHandleChange = (event: any) => {
    setData(event.target.name, event.target.value);
  };

  const submit = (e: any) => {
    e.preventDefault();

    post(route('password.update'));
  };

  return (
    <Layout title="Reset Password">
      <div className="mx-auto max-w-lg px-4 sm:px-6 lg:px-8">
        <form onSubmit={submit}>
          <div>
            <InputLabel forInput="email" value="Email" />

            <TextInput
              type="email"
              name="email"
              value={data.email}
              className="mt-1 block w-full"
              autoComplete="username"
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
              autoComplete="new-password"
              isFocused={true}
              handleChange={onHandleChange}
            />

            <InputError message={errors.password} className="mt-2" />
          </div>

          <div className="mt-4">
            <InputLabel forInput="password_confirmation" value="Confirm Password" />

            <TextInput
              type="password"
              name="password_confirmation"
              value={data.password_confirmation}
              className="mt-1 block w-full"
              autoComplete="new-password"
              handleChange={onHandleChange}
            />

            <InputError message={errors.password_confirmation} className="mt-2" />
          </div>

          <div className="flex items-center justify-end mt-4">
            <PrimaryButton className="ml-4" processing={processing}>
              Reset Password
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Layout>
  );
}
