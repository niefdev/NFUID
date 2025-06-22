from setuptools import setup

with open("README.md", "r", encoding="utf-8") as f:
    long_description = f.read()

setup(
    name="nfuid",
    version="2.0.6",
    author="niefdev",
    author_email="niefdev@gmail.com",
    description="Minimal 11-character URL-safe unique ID generator with hidden mode and timestamp-based structure.",
    long_description=long_description,
    long_description_content_type="text/markdown",
    url="https://github.com/niefdev/nfuid",
    py_modules=["nfuid"],
    classifiers=[
        "Development Status :: 5 - Production/Stable",
        "Intended Audience :: Developers",
        "License :: OSI Approved :: MIT License",
        "Operating System :: OS Independent",
        "Programming Language :: Python :: 3",
        "Programming Language :: Python :: 3.6",
        "Programming Language :: Python :: 3.7",
        "Programming Language :: Python :: 3.8",
        "Programming Language :: Python :: 3.9",
        "Programming Language :: Python :: 3.10",
    ],
    python_requires=">=3.6",
    install_requires=[],
    keywords="id unique-id short-id uuid identifier base64 timestamp url-safe nfuid",
    project_urls={
        "Bug Reports": "https://github.com/niefdev/nfuid/issues",
        "Source": "https://github.com/niefdev/nfuid",
    },
)