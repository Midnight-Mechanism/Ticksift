import { useForm } from '@inertiajs/react';

import PrimaryButton from '@/Components/PrimaryButton';
import Layout from '@/Layouts/Layout';

export default function VerifyEmail({ status }: { status: string }) {
  const { post, processing } = useForm();

  const submit = (e: any) => {
    e.preventDefault();

    post(route('verification.send'));
  };

  return (
    <Layout title="Email Verification">
      <div className="mx-auto max-w-lg px-4 sm:px-6 lg:px-8">
        <div className="mb-4 text-sm">
          Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we
          sent you? If you did not receive the email, we will gladly send you another.
        </div>

        {status === 'verification-link-sent' && (
          <div className="mb-4 font-medium text-sm text-green-300">
            A new verification link has been sent to the email address you provided during registration.
          </div>
        )}

        <form onSubmit={submit}>
          <div className="mt-4 flex items-center justify-between">
            <PrimaryButton processing={processing}>Resend Verification Email</PrimaryButton>
          </div>
        </form>
      </div>
    </Layout>
  );
}
