import { useForm } from '@inertiajs/react';

import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import Layout from '@/Layouts/Layout';

export default function ForgotPassword({ status }: { status: any }) {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
  });

  const onHandleChange = (event: any) => {
    setData(event.target.name, event.target.value);
  };

  const submit = (e: any) => {
    e.preventDefault();

    post(route('password.email'));
  };

  return (
    <Layout title="Forgot Password">
      <div className="mx-auto max-w-lg px-4 sm:px-6 lg:px-8">
        <div className="mb-4 text-sm leading-normal">
          Forgot your password? No problem. Just enter your email address and we will email you a password reset link
          that will allow you to choose a new one.
        </div>

        {status && <div className="mb-4 font-medium text-sm text-green-300">{status}</div>}

        <form onSubmit={submit}>
          <TextInput
            type="text"
            name="email"
            value={data.email}
            className="mt-1 block w-full"
            isFocused={true}
            handleChange={onHandleChange}
          />

          <InputError message={errors.email} className="mt-2" />

          <div className="flex items-center justify-end mt-4">
            <PrimaryButton className="ml-4" processing={processing}>
              Email Password Reset Link
            </PrimaryButton>
          </div>
        </form>
      </div>
    </Layout>
  );
}
