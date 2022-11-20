import { Head, Link, useForm, usePage } from '@inertiajs/inertia-react';
import { useEffect } from 'react';
import ReCAPTCHA from 'react-google-recaptcha';

import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import Layout from '@/Layouts/Layout';

export default function Register() {
  const { recaptchaKey } = usePage<any>().props;
  const { data, setData, post, processing, errors, reset } = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    password_confirmation: '',
    'g-recaptcha-response': '',
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

    post(window.route('register'));
  };

  return (
    <Layout>
      <Head title="Register" />

      <div className="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <form onSubmit={submit}>
          <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <InputLabel forInput="first_name" value="First Name" />

              <TextInput
                type="text"
                name="first_name"
                value={data.first_name}
                className="mt-1 block w-full"
                autoComplete="given-name"
                isFocused={true}
                handleChange={onHandleChange}
              />

              <InputError message={errors.first_name} className="mt-2" />
            </div>
            <div>
              <InputLabel forInput="last_name" value="Last Name" />

              <TextInput
                type="text"
                name="last_name"
                value={data.last_name}
                className="mt-1 block w-full"
                autoComplete="family-name"
                handleChange={onHandleChange}
              />

              <InputError message={errors.last_name} className="mt-2" />
            </div>
          </div>

          <div className="mt-4">
            <InputLabel forInput="email" value="Email" />

            <TextInput
              type="email"
              name="email"
              value={data.email}
              className="mt-1 block w-full"
              autoComplete="username"
              handleChange={onHandleChange}
              required
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
              handleChange={onHandleChange}
              required
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
              handleChange={onHandleChange}
              required
            />

            <InputError message={errors.password_confirmation} className="mt-2" />
          </div>

          <div className="flex flex-wrap items-center justify-between items-end">
            <div className="mt-4">
              <ReCAPTCHA
                theme="dark"
                sitekey={recaptchaKey}
                onChange={token => setData('g-recaptcha-response', token ?? '')}
              />
              <InputError message={errors['g-recaptcha-response']} className="mt-2" />
            </div>

            <div className="mt-4">
              <Link href={window.route('login')} className="underline text-sm hover:text-blue-100">
                Already registered?
              </Link>

              <PrimaryButton className="ml-4" processing={processing}>
                Register
              </PrimaryButton>
            </div>
          </div>
        </form>
      </div>
    </Layout>
  );
}
